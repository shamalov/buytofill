<?

    #test v5
    $d = json_decode(file_get_contents('php://input'), 1);
    http_response_code(200);
    $ch = curl_init();
    #curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    if($d['fromAddress'] == "forwarding-noreply@google.com"){
        $plus = explode('+', explode('@', $d['toAddress'])[0])[1];
        if (strlen($plus) == 5 && preg_match('/^[A-Za-z]+$/', $plus)) { #preg_match('/^[A-Za-z]+$/', $plus)
            $url = "https://mail.google.com/mail/vf-".substr($d['html'],strpos($d['html'],"%5B"),118);
            
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_exec($ch);
            curl_close();
        }
    } # Accept Forwarding | Add Auth for BTF ID | #https://developers.google.com/gmail/api/reference/rest/v1/users.settings.forwardingAddresses/create
    
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://accounts.zoho.com/oauth/v2/token",
        CURLOPT_POSTFIELDS => http_build_query([
            'client_id' => "1000.FXT2V82FAZPDXSQL6BGHFL3E4T0DJC",
            'client_secret' => "bda8db8e2217d787206d1e5563506df58152c09a95",
            'grant_type' => "client_credentials",
            'scope' => "ZohoMail.messages.DELETE,ZohoMail.messages.READ"
        ])
    ]); # Authorize Zoho
    $token = json_decode(curl_exec($ch), 1)['access_token'];
    
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://mail.zoho.com/api/accounts/8890026000000024025/folders/".$d['folderId']."/messages/".$d['messageId']."/header?raw=false",
        CURLOPT_HTTPHEADER => ["Authorization: Bearer ".$token],
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPGET => 1
    ]); # Get BTF-ID | Replace using google sign in
    $header = curl_exec($ch);
    
    foreach(file(__DIR__.'/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line){
        list($name, $value) = explode('=', $line, 2);
        putenv("$name=$value");
    } # Env
    
    if($d['fromAddress'] == "BestBuyInfo@emailinfo.bestbuy.com"){
        $retailer = 0;
        
        if($d['subject'] == "Thanks for your order."){
            $data = $d['html'];
            $ref = substr($data, strpos($data, 'BBY01-') + 6, 12);
            file_put_contents('emails/email_log.txt', print_r($ref, 1));
            
            curl_setopt_array($ch, [
                CURLOPT_URL => "https://click.emailinfo2.bestbuy.com/?qs=".substr($data, strpos($data, 'View O') - 394, 128),
                CURLOPT_HEADER => 1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_NOBODY => 1,
                CURLOPT_HTTPHEADER => [],
            ]); # Email Tokens
            $t = curl_exec($ch);
            file_put_contents('emails/email_log.txt', print_r(t, 1), FILE_APPEND);
            $s1 = strpos($t,'t1');
            $s2 = strpos($t,'t2');
            curl_setopt_array($ch, [
                CURLOPT_URL => "https://www.bestbuy.com/profile/ss/orders/email-redirect/order-status?t1=".substr($t,$s1+5,$s2-$s1-18)."&t2=".substr($t,$s2+5,43),
                CURLOPT_USERAGENT => "/"
            ]); # Visiter Tokens
            $v = curl_exec($ch);
            #file_put_contents('emails/email_log.txt', print_r($v, 1));
            curl_setopt_array($ch, [
                CURLOPT_URL => "https://www.bestbuy.com/profile/ss/api/v1/orders/BBY01-".$reference,
                CURLOPT_COOKIE => "vt=".substr($v,strpos($v,'vt')+3,36)."; SID;",
                CURLOPT_NOBODY => 0,
                CURLOPT_HEADER => 0
            ]); # Retrieve Order JSON
            $bby = json_decode(curl_exec($ch));
            file_put_contents('emails/email_log.txt', print_r($bby, 1), FILE_APPEND);
            $content = [];
            foreach ($bby->order->items as $item){
                if(isset($content[$item->sku])){
                    $content[$item->sku] += $item->quantity;
                }else{
                    $content[$item->sku] = $item->quantity;
                }
            } # Retrieve Order Contents
            
            $create = true;
            
        }elseif($d['subject'] == "We have your tracking number."){
            $data = $d['html'];
        }elseif($d['subject'] == "Your Best Buy order has been canceled."){
            $data = $d['html'];
            $ref = substr($data, strpos($data, 'BBY01-') + 6, 12);
            
            //step = 1
            $conn = new mysqli(getenv('DATABASE_HOST'), getenv('DATABASE_USER'), getenv('DATABASE_PASS'), getenv('DATABASE_NAME'));
            $stmt = $conn->prepare("UPDATE `retailerOrders` SET `status` = 0 WHERE `ref` = ?");
            $stmt->bind_param("s", $ref);
            $stmt->execute();
            
            exit;
        }
    }
    
    if($create){
        $p = json_decode($header, true)['data']['headerContent']['Delivered-To'][0]; # AAAAA
        $uid = (ord($p[4])-64)*(ord($p[5])-64)*(ord($p[6])-64)*(ord($p[7])-64)*(ord($p[8])-64); # 1
        
        date_default_timezone_set('America/New_York');
        $date = date('mdHi');
        
        $conn = new mysqli(getenv('DATABASE_HOST'), getenv('DATABASE_USER'), getenv('DATABASE_PASS'), getenv('DATABASE_NAME'));
        if(($d['subject'] == "Your Best Buy order has been canceled.")){
            $stmt = $conn->prepare("INSERT INTO `retailerOrders` (ref, retailer, date, status) VALUES (?, ?, ?, 0);");
            $stmt->bind_param("sii", $ref, $retailer, $date);
        }else{
            $stmt = $conn->prepare("INSERT INTO `retailerOrders` (ref, retailer, date) VALUES (?, ?, ?);");
            $stmt->bind_param("sii", $ref, $retailer, $date);
        }
        $stmt->execute();
        $stmt->close();
        $rid = $conn->insert_id;
        
        $stmt = $conn->prepare("INSERT INTO `commit` (uid, oid, rid, qty) VALUES (?, (SELECT o.id FROM `retailerKeys` rk INNER JOIN `order` o ON o.pid = rk.id WHERE rk.retailer = ? AND rk.ref = ? AND o.status = 1), ?, ?);");
        foreach ($content as $sku => $quantity) {
            try{
                $stmt->bind_param("iisii", $uid, $retailer, $sku, $rid, $quantity);
                $stmt->execute();
            }catch(Exception $e){
                file_put_contents('emails/email_log.txt', "Make sure there is an order for $sku and retailerKeys.retailer = $retailer and retailerKeys.ref = $sku exists (" . $e->getMessage() . ")\n", FILE_APPEND);
            }
        }
        
        $stmt->close();
        $conn->close();
    }
    
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://mail.zoho.com/api/accounts/8890026000000024025/folders/".$d['folderId']."/messages/".$d['messageId'],#."?expunge=true",
        CURLOPT_CUSTOMREQUEST => "DELETE"
    ]);
    curl_exec($ch);
    curl_close($ch);

    exit;
?>
