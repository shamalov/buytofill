<?
    session_start();        
    require('main/env.php');
    /*
    if ($_SERVER['REQUEST_METHOD'] == "GET") {
        if (isset($_SESSION['role']) && $_SESSION['role'] == "staff") {
            $_SESSION['cM'] = 'AES-256-CBC';
            $_SESSION['cK'] = openssl_random_pseudo_bytes(32);
            $_SESSION['cI'] = openssl_random_pseudo_bytes(openssl_cipher_iv_length($_SESSION['cM'])); 
            
            function enc($string) {
                return openssl_encrypt($string,$_SESSION['cM'],$_SESSION['cK'],0,$_SESSION['cI']);
            }
        }else{
            
            header('Location: login');
        }
    }*/
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $conn = new mysqli(getenv('DATABASE_HOST'), getenv('DATABASE_USER'), getenv('DATABASE_PASS'), getenv('DATABASE_NAME'));
        
        function dav($encryption) {
            return filter_var(openssl_decrypt($encryption, $_SESSION['cM'], $_SESSION['cK'], 0, $_SESSION['cI']), FILTER_VALIDATE_INT) ?: false;
        }
        
        $uid = $_SESSION['uid'];
        if(isset($_POST['itemIndex'])){
            
            $index = dav($_POST['itemIndex']);
            
            $conn->begin_transaction();
            
            try {
                $stmt = $conn->prepare("
                    UPDATE `buyer`
                    SET cart = IF(
                        FIND_IN_SET(?, cart),
                        cart,
                        IF(cart IS NULL, ?, CONCAT(cart, ',', ?))
                    ) WHERE id = ?
                ");
                $stmt->bind_param("iiii", $index, $index, $index, $uid);
                
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $conn->commit();
                        echo 1;
                    } else {
                        $conn->rollback();
                        echo 0;
                    }
                } else {
                    $conn->rollback();
                    echo 0;#json_encode("Error: " . $stmt->error);
                }
                $stmt->close();
            } catch (Exception $e) {
                $conn->rollback();
                echo 0;#"Transaction failed: " . $e->getMessage();
            }
        }   
        else if(isset($_POST['orderData'])){
            
            $data = json_decode($_POST['orderData'], true);
            $values = [];
            $placeholders = [];
            $types = "";
            
            foreach ($data as &$item) {
                $item['index'] = dav($item['index']);
                $values[] = $item['index'];
                $values[] = $uid;
                $values[] = $item['qty'];
                $placeholders[] = '(?, ?, ?)';
                $types .= "iii";
            }
            
            $conn->begin_transaction();
            
            try {
                $sql = 'INSERT INTO `order` (`pid`, `uid`, `qty`) VALUES ' . implode(', ', $placeholders);
                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$values);
                $stmt->execute();
                
                if ($stmt->error) throw new Exception($stmt->error);
                
                $update_stmt = $conn->prepare('UPDATE buyer SET cart = NULL WHERE id = ?');
                $update_stmt->bind_param('i', $uid);
                $update_stmt->execute();
            
                if ($update_stmt->error) throw new Exception($update_stmt->error);
                
                $conn->commit();
                $update_stmt->close();
                $stmt->close();
            } catch (Exception $e) {
                $conn->rollback();
                echo 'Error: ' . $e->getMessage();
            }
        }
        else if(isset($_POST['removeItem'])){
            
            $itemToRemove = dav($_POST['removeItem']);
            $conn->begin_transaction();
            
            try {
                $stmt = $conn->prepare("
                    UPDATE buyer
                    SET cart = CASE
                                   WHEN TRIM(BOTH ',' FROM REPLACE(CONCAT(',', cart, ','), CONCAT(',', ?, ','), ',')) = ''
                                   THEN NULL
                                   ELSE TRIM(BOTH ',' FROM REPLACE(CONCAT(',', cart, ','), CONCAT(',', ?, ','), ','))
                               END
                    WHERE id = ?
                ");
                $stmt->bind_param("iii", $itemToRemove, $itemToRemove, $uid);
                $stmt->execute();
                if ($stmt->error) throw new Exception($stmt->error);
                
                $conn->commit();
                $stmt->close();
            } catch (Exception $e) {
                $conn->rollback();
                echo 'Error: ' . $e->getMessage();
            }
        }
        
        $conn->close();
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <?require('main/head.php')?>
        <title>BuyToFill</title>
        <link rel="stylesheet" href="main/styles.css"/>
        
        <style>
            #searchw{
                display:flex;
                position:relative;
                align-items:center;
                width:100%;
            }
            #hm-conditions .hmc-selector {
                display:flex;
                gap:.5rem;
                padding: 0 1rem;
                margin-bottom:.5rem;
                #searchbar-icon{
                    fill:none;
                    color: #999;
                    position:absolute;
                    left: .8rem;
                    min-width: 20px;
                    width: 20px;
                    path{
                        stroke:currentColor;
                        stroke-width:1.5px;
                        stroke-linecap: round;
                        stroke-linejoin: round;
                    }
                }
                &:has(input:hover) #searchbar-icon, &:has(input:not(:placeholder-shown)) #searchbar-icon{
                    color: var(--grn);
                }
                input{
                    background: #1a1a1a;
                    border-radius: .2rem;
                    outline:0;
                    color: var(--grn);
                    font-size:.9rem;
                    font-weight:600;
                    height:100%;
                    padding: .5rem 1rem .5rem calc(1.4rem + 20px);
                    width: 100%;
                    border: 1px solid #222;
                    &:hover, &:focus-visible{
                        border-color: var(--grn);
                    }
                }
                h4{
                    background: #1a1a1a;
                    display:flex;
                    text-wrap:nowrap;
                    padding: .7rem 1rem;
                    border-radius: .2rem;
                    color: #aaa;
                    font-size:.85rem;
                    cursor:pointer;
                    align-items:center;
                    justify-content:center;
                    border: 1px solid #222;
                    svg{
                        width:20px;
                        fill:none;
                        margin-right:.5rem;
                        path{
                            stroke:currentColor;
                            stroke-width:1.5px;
                            stroke-linecap: round;
                            stroke-linejoin: round;
                        }
                    }
                    &:hover{
                        color:var(--grn);
                        border-color: var(--grn);
                        svg path{
                            stroke:currentColor;
                        }
                    }
                }
            }
            #hold-main{
                background:#151515;
                width:100%;
                border-radius:.25rem;   
                overflow: hidden;
                border: 1px solid #333;
                position:relative;
            }
            #hm-conditions{
                height: var(--con-h);
                z-index:0;
                padding:0 0 1rem 0;
                transition: height .5s ease;
                position:relative;
                .hmc-slider{
                    opacity:0;
                    transition: opacity .5s ease;
                }
                #hmc-i{
                    opacity:0;
                    transition: opacity .5s ease;
                }
                >p{
                    color:var(--grn);
                    font-size:.8rem;
                    padding: .5rem 1rem .5rem;
                    font-weight:bold;
                    cursor:pointer;
                    transition: padding .5s ease;
                }
                
            }
            .hmc-slider{
                display: flex;
                padding: 0 1rem;
                overflow-x: scroll;
                overflow-y: hidden;
                >div,a{
                    border:1px solid #222 !important;
                }
                & div:not(:first-child){
                    margin-left:.5rem;
                }
                &::-webkit-scrollbar {
                    height: 2px;
                }
                &::-webkit-scrollbar-track {
                    background: transparent;
                }
                &::-webkit-scrollbar-thumb {
                    background: #4e8768;
                    border-radius:.2rem;
                }
                &:hover::-webkit-scrollbar-thumb {
                    background: var(--grn);
                }
                a:hover, div:hover{
                    color:var(--grn) !important;
                    border: 1px solid var(--grn) !important;
                }
            }
            #hmcs-b{
                padding: 0 1rem 2px;
                div{
                    min-width:64px;
                    height:64px;
                    border-radius:.2rem;
                    background: #1a1a1a;
                    cursor:pointer;
                    svg{
                        width: 64px;
                        fill:#999;
                        height:100%;
                    }
                    &:hover svg{
                        fill: var(--grn);
                    }
                }
            }
            #hmcs-s{
                margin-top:calc(.25rem - 2px);
                padding: 2px 1rem;
                margin-bottom: .25rem;
                a{
                    background: #1a1a1a;
                    padding:.5rem;
                    color: #999;
                    text-wrap:nowrap;
                    cursor:pointer;
                    border-radius:.2rem;
                    font-size:.8rem;
                    &:not(:first-child){
                        margin-left:.5rem;
                    }
                }
            }
            .hmcnb{
                color: #999;
                background: #1a1a1a;
                border-radius:.2rem;
                cursor:pointer;
                padding:.5rem;
                text-wrap:nowrap;
                font-size:.8rem;
                display:flex;
                align-items:center;
                border:1px solid #222;
                &:hover{
                    color:var(--grn);
                    border-color: var(--grn);
                    path{
                        stroke: currentColor;
                    }
                }
            }
            #hmci-e{
                box-sizing:content-box;
                padding:.5rem .75rem;
                svg{
                    fill:none;
                    width:15px;
                    margin-left:.25rem;
                    path{
                        stroke: currentColor;
                        stroke-width:1.5px;
                        stroke-linecap: round;
                        stroke-linejoin: round;
                    }
                }
                &:hover{
                    color: var(--grn);
                }
            }
            #hmc-i{
                margin:0 1rem;
                display:flex;
                gap:.5rem;
                aside{
                    margin-left:auto;
                }
            }
            #hm-teller{
                background:#1a1a1a;
                border-top: 1px solid #2b2b2b;
                height:calc(100% - var(--con-h));
                border-radius:.25rem;
                padding:1rem;
                position: absolute;
                width: 100%;
                transition: height .5s ease;
                z-index:2;
                overflow-y:scroll;
                > div{
                    background:#222;
                    border-radius:.2rem;
                    display:flex;
                    overflow:hidden;
                    font-size:.9rem;
                    box-shadow: 0px 0px 2px #111;
                    border: 1px solid #222;
                    >.side-badge{
                        margin-right: -1.5rem;
                        text-align:center;
                        box-shadow: 1px 0px 2px #111111aa;
                        writing-mode: vertical-rl;
                        transform:rotate(180deg);
                        direction: ltr;
                        padding: .5rem .3rem .5rem .7rem;
                        font-weight:bold;
                        border-top-right-radius: .5rem;
                        border-bottom-right-radius: .5rem;
                        font-size:.6rem;
                        transition: margin .3s ease;
                        &.purp{
                            background: var(--purp);
                            color:#fff;
                        }
                        &.grn{
                            background: var(--grn);
                        }
                        &.www{
                            background: var(--bg-text);
                        }
                    }
                    > .hmt-main{
                        box-shadow: 1px 0px 2px #111;
                        width:100%;
                        background: #222;
                        padding:1rem;
                        border-radius:.2rem;
                        z-index:1;
                        color:#fff;
                    }
                    &:not(:last-child){
                        margin-bottom:.5rem;
                    }
                    &:hover{
                        >.side-badge{
                            margin-right: -.5rem;
                        }
                    }
                }
                &::-webkit-scrollbar {
                    width: 10px;
                }
                &::-webkit-scrollbar-track {
                    background: transparent;
                }
                &::-webkit-scrollbar-thumb {
                    background: #4e8768;
                    border-radius:.25rem;
                }
                &:hover::-webkit-scrollbar-thumb {
                    background: var(--grn);
                }
            }
            #hold-main:has(#hm-conditions.show){
                --con-h:255px;
            }
            #hm-conditions:is(.show){
                >p{
                    padding: 1rem 1rem .5rem;
                }
                .hmc-slider, #hmc-i{
                    opacity:1;
                }
            }
            .hmt-main > aside{
                margin-top:.8rem;
                position:relative;
                display:flex;
                padding-top:.8rem;
                justify-content: space-between;
                gap: 1.5rem;
                >div{
                    display:flex;
                    gap: 1.5rem;
                }
                div>span{
                    font-size:.7rem;
                    font-weight:1000;
                    color: #999;
                    text-transform: uppercase;
                }
                div>p{
                    font-weight:500;
                    color: #999;
                    text-wrap:nowrap;
                }
                >aside{
                    display:flex;
                    gap: 1.5rem;
                    div{
                        text-align:right;
                    }
                    .hmt-a{
                        background: var(--purp);
                        height: 100%;
                        padding:0 1rem;
                        cursor:pointer;
                        border-radius: .2rem;
                        font-size:.9rem;
                        text-transform:unset;
                        display: flex;
                        font-weight:bold;
                        color:#fff;
                        align-items: center;
                    }
                }
                &:after{
                    content:'';
                    position:absolute;
                    background:#333;
                    width:100%;
                    top:0;
                    left:0;
                    height: 1px;
                }
            }
            .hmt-text{
                text-wrap:nowrap;
            }
            #hold-addToCart{
                border: 1px solid #333;
                background: #151515;
                width:550px;
                margin-left:1.5rem;
                border-radius:.25rem;
                padding:0 1rem 1rem;
                display:flex;
                -ms-flex-direction: column;
                flex-direction:column;
                >div{
                    height:100%;
                    overflow-y:scroll;
                    .hmc-item{
                        background:#1a1a1a;
                        margin-bottom:.5rem;
                        border-radius:.2rem;
                        border:  1px solid #222;
                        color: #eee;
                        overflow:hidden;
                        span{
                            padding:1rem;
                            display:block;
                            font-size:.9rem;
                        }
                        div{
                            display:flex;
                            font-size:.9rem;
                            font-weight:500;
                            input{
                                background:#222;
                                border:0;
                                width:100%;
                                font-size:.9rem;
                                font-weight:500;
                                text-align:right;
                                color:#ccc;
                                outline:0;
                                padding: .8rem 1rem;
                                border-top-right-radius: .5rem;
                            }
                            div{
                                display:flex;
                                color:#ddd;
                                align-items:center;
                                padding: .3rem .8rem;
                            }
                            svg{
                                padding: .3rem .8rem;
                                width: 20px;
                                fill: #555;
                                margin-right: -47px;
                                cursor:pointer;
                                min-width: 20px;
                                box-sizing: content-box;
                                transition:margin .3s ease;
                            }
                        }
                        &:hover svg{
                            margin-right:0;
                        }
                        &:first-child{
                            margin-top:1rem;
                        }
                    }
                    &::-webkit-scrollbar {
                        display:none;
                    }
                }
                >button{
                    width:100%;
                    margin-top:1rem;
                    border-radius:.2rem;
                    font-size:1rem;
                    padding:1rem;
                    outline:0;
                    border:0;
                    color:white;
                    font-weight:bold;
                    background:var(--purp);
                    cursor:pointer;
                }
            }
            #hold{
                display:flex;
                height:calc(100% - .5rem);
            }
        </style>
    </head>
    <body>
        <?require('main/nav.php')?>
        <div id="content-wrapper">
            <?require('main/header.php')?>
            <div id="content">
                <div id="hold-main">
                    <div id="hm-conditions">
                        <p id="hmc-toggle" onclick="document.getElementById('hm-conditions').classList.toggle('show');">Showing all results</p><?
                        
        $conn = new mysqli(getenv('DATABASE_HOST'), getenv('DATABASE_USER'), getenv('DATABASE_PASS'), getenv('DATABASE_NAME'));
        $uid = $_SESSION['uid'];
        $stmt = $conn->prepare("
            SELECT 
                i.id, 
                i.name, 
                i.color, 
                i.modelNumber, 
                i.manufacturer, 
                i.type, 
                i.stock, 
                i.ETA, 
                i.price, 
                CASE 
                    WHEN FIND_IN_SET(i.id, b.cart) > 0 THEN TRUE 
                    ELSE FALSE 
                END AS added
            FROM `item` i
            LEFT JOIN `buyer` b ON b.id = ?
            WHERE i.stock > 0
        ");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $result = $stmt->get_result();
        $manufacturers = [];
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $allData[] = $row;
            }
        }
        
                      ?><div class="hmc-selector">
                            <h4>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path d="m15.3 18.2c-2 2.7-3 4-3.9 3.8-.9-.3-.9-2-.9-5.2l0-.4c0-1.1 0-1.7-.4-2.1l0 0c-.4-.4-1-.4-2.3-.4-2.2 0-3.3 0-3.7-.7 0 0 0 0 0 0-.4-.7.3-1.5 1.6-3.3l3-4.1c2-2.7 3-4 3.9-3.8.9.3.9 2 .9 5.2v.4c0 1.1 0 1.7.4 2.1l0 0c.4.4 1 .4 2.3.4 2.2 0 3.3 0 3.7.7 0 0 0 0 0 0 .4.7-.3 1.5-1.6 3.3"/>
                                </svg>
                                Trending
                            </h4>
                            <h4>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path d="m15.4 15.4c.6-.6.7-1.5.2-2-.5-.4-1.4-.4-2 .2-.6.6-1.4.7-1.9.2-.5-.5-.4-1.4.2-1.9m3.5 3.5.3.3m-.3-.3c-.4.4-.9.6-1.4.5m-2.5-4.4.4.4m0 0c.3-.4.7-.5 1.1-.5"/>
                                    <path d="m8.6 10.9c1.1 0 2-.9 2-2 0-1.1-.9-2-2-2-1.1 0-2 .9-2 2"/>
                                    <path d="m16.1 4.7c-1.5-1.5-2.3-2.3-3.3-2.6-1-.3-2.1 0-4.2.5l-1.2.3c-1.8.4-2.7.6-3.3 1.2-.6.6-.8 1.5-1.2 3.3l-.3 1.2c-.5 2.1-.8 3.2-.5 4.2.3 1 1.1 1.8 2.6 3.3l1.9 1.9c2.6 2.7 4 4 5.7 4 1.6 0 3-1.3 5.7-4 2.7-2.7 4-4.1 4-5.7 0-1.4-.9-2.5-2.6-4.3"/>
                                </svg>
                                Discounted
                            </h4>
                            <h4>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path d="m22 10c-.1-1.3-.2-2.1-.6-2.9-.6-1-1.7-1.5-3.8-2.7l-2-1c-1.8-.9-2.7-1.4-3.6-1.4-.9 0-1.8.5-3.6 1.4l-2 1c-2.1 1.2-3.2 1.7-3.8 2.7-.6 1.1-.6 2.3-.6 4.8v.2c0 2.5 0 3.7.6 4.8.6 1 1.7 1.5 3.8 2.7l2 1c1.8.9 2.7 1.4 3.6 1.4.9 0 1.8-.5 3.6-1.4l2-1c2.1-1.2 3.2-1.7 3.8-2.7.4-.8.5-1.6.6-2.9"/>
                                    <path d="m21 7.5-9 4.5m0 0-9-4.5m9 4.5v9.5"/>
                                </svg>
                                New Deals
                            </h4>
                            <h4>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path d="m20 12c0-4.4-3.6-8-8-8m0 16c2.5 0 4.8-1.2 6.2-3"/>
                                    <path d="m4 12h10m0 0-3-3m3 3-3 3"/>
                                </svg>
                                Just Sold
                            </h4>
                            <div id="searchw">
                                <svg id="searchbar-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="m11 6c2.8 0 5 2.2 5 5m.7 5.7 4.3 4.3m-2-10c0 4.4-3.6 8-8 8-4.4 0-8-3.6-8-8 0-4.4 3.6-8 8-8 4.4 0 8 3.6 8 8z"></path>
                                </svg>
                                <input id="sw-search" type="text" placeholder="Search">
                            </div>
                        </div>
                        <div id="hmcs-b" class="hmc-slider"><? 
                            
        $manufacturerSVG = [
            1 /*Google*/ => "m41.5 28.8c.25.1.5.3.7.5 0-.2 0-.4 0-.55l1.4 0v6.1c0 .95-.25 1.9-.9 2.6-.7.75-1.85.95-2.85.8a3.2 3.2 90 01-2.4-1.95c.4-.2.85-.35 1.25-.55a1.9 1.9 90 001.35 1.15c.6.1 1.3-.05 1.7-.55.4-.55.4-1.25.4-1.9a2.45 2.45 90 01-1.1.7 3 3 90 01-2.75-.7 3.45 3.45 90 01.45-5.45 2.8 2.8 90 012.7-.2zm-13.55.4a3.25 3.25 90 011.3 2.1 3.45 3.45 90 01-.65 2.8 3.35 3.35 90 01-2.85 1.25 3.35 3.35 90 01-2.5-1.35 3.5 3.5 90 01-.45-3.15 3.25 3.25 90 012.5-2.15 3.45 3.45 90 012.65.55zm-10.55-4.4a4.95 4.95 90 013.4 1.45c-.35.35-.65.65-1 1a3.7 3.7 90 00-4.6-.4 3.75 3.75 90 00-1.65 2.65 3.9 3.9 90 00.85 3c.65.8 1.65 1.3 2.7 1.35a3.55 3.55 90 002.65-.95 2.95 2.95 90 00.9-1.9l-2.3 0h-1.15v-1.45h4.8c.25 1.55-.1 3.25-1.25 4.35a4.7 4.7 90 01-2.9 1.3 5.35 5.35 90 01-5.55-3.45c-.4-1.1-.4-2.35-.05-3.45a5.35 5.35 90 014.8-3.5m18.5 4.6a3.45 3.45 90 01.35 4.6 3.35 3.35 90 01-2.75 1.25 3.3 3.3 90 01-2.6-1.35 3.5 3.5 90 01-.45-3.2 3.25 3.25 90 012.5-2.1 3.4 3.4 90 012.95.8m15.5-.7c1 .4 1.65 1.35 1.95 2.35l-4.45 1.85a1.8 1.8 90 00.95.9c.6.2 1.3.15 1.8-.25.2-.15.35-.35.5-.55l1.15.75a3.3 3.3 90 01-2.4 1.45 3.3 3.3 90 01-2.9-1.15 3.6 3.6 90 01.25-4.75 3.1 3.1 90 013.15-.65m-4.9-3.5v9.9c-.5 0-1 0-1.5 0v-9.9h1.45m-5.85 4.7c-.4.05-.8.3-1.1.65a2.25 2.25 90 000 2.8c.35.45.95.7 1.55.65a1.75 1.75 90 001.3-.9c.45-.8.4-1.9-.25-2.6a1.7 1.7 90 00-1.55-.55m-7.35 0a1.9 1.9 90 00-.95.6 2.2 2.2 90 00.1 2.85 1.85 1.85 90 002.95-.4c.45-.85.3-2-.4-2.6a1.85 1.85 90 00-1.7-.45m-7.3 0a1.85 1.85 90 00-.95.6 2.2 2.2 90 00.1 2.85 1.8 1.8 90 002.9-.45c.45-.8.35-1.95-.35-2.6a1.85 1.85 90 00-1.7-.45m23.1.75a2 2 90 00-.3 1.15l3-1.25c-.15-.4-.55-.65-.95-.7a1.75 1.75 90 00-1.7.8",
            2 /*Amazon*/ => "m35.8 26.8v-1a.3.3 90 01.2-.3h4.8c.2 0 .3.1.3.3v.9c0 .1-.1.3-.4.6l-2.4 3.5c.9 0 1.8.1 2.7.6.2.1.2.3.2.4v1.2c0 .1-.1.3-.3.2a5.6 5.6 90 00-5.1 0c-.1.1-.3-.1-.3-.2v-1.1c0-.2 0-.5.2-.7l2.8-4.1h-2.5c-.1 0-.2-.1-.2-.3m-17.5 6.6h-1.4a.3.3 90 01-.3-.2v-7.4c0-.2.1-.3.3-.3h1.3c.2 0 .3.1.3.3v.9h0c.4-.9 1.1-1.3 2-1.3.9 0 1.4.4 1.8 1.3a2.1 2.1 90 012.1-1.3c.6 0 1.2.2 1.6.8.5.6.4 1.5.4 2.3v4.6c0 .2-.1.3-.3.3h-1.4a.3.3 90 01-.3-.3v-3.9c0-.3 0-1.1 0-1.3-.1-.5-.5-.7-.9-.7-.3 0-.7.2-.9.6-.1.4-.1 1-.1 1.4v3.9a.3.3 90 01-.3.3h-1.4a.3.3 90 01-.3-.3v-3.9c0-.8.1-2-.9-2-1 0-1 1.2-1 2l0 4a.3.3 90 01-.3.2m26.9-8c2.2 0 3.3 1.8 3.3 4.1 0 2.3-1.2 4.1-3.3 4.1-2.1 0-3.3-1.9-3.3-4.1 0-2.3 1.2-4.1 3.3-4.1m0 1.5c-1 0-1.1 1.4-1.1 2.3 0 .9 0 2.8 1.1 2.8 1.1 0 1.2-1.5 1.2-2.4 0-.7 0-1.4-.2-2-.2-.5-.5-.7-1-.7m6.1 6.5h-1.4a.3.3 90 01-.3-.3v-7.3a.3.3 90 01.3-.3h1.4a.3.3 90 01.2.2v1.2h0c.4-1 1-1.5 2-1.5.7 0 1.3.2 1.7.8.4.6.4 1.6.4 2.3v4.7a.3.3 90 01-.3.2h-1.4a.3.3 90 01-.3-.2v-4c0-.8.1-2-.9-2-.4 0-.7.2-.8.6-.2.4-.3.9-.3 1.4v3.9a.3.3 90 01-.3.3zm-35.8-1.3c-.3-.4-.6-.7-.6-1.4v-2.2c0-1 .1-1.8-.6-2.5-.6-.5-1.5-.7-2.2-.7-1.4 0-2.9.5-3.2 2.2-.1.2 0 .3.2.3l1.4.1c.1 0 .2-.1.2-.2.2-.6.6-.9 1.2-.9.3 0 .6.1.8.4.2.3.2.7.2 1v.2c-.9.1-2 .2-2.7.5a2.4 2.4 90 00-1.6 2.4c0 1.5 1 2.2 2.2 2.2 1 0 1.6-.2 2.4-1 .2.3.3.5.8.9a.3.3 90 00.4 0 65.5 65.5 90 011.1-1c.1-.1.1-.2 0-.3m-2.9-.7c-.2.4-.6.7-1 .7-.5 0-.9-.5-.9-1.1 0-1.2 1.1-1.4 2.2-1.4v.3c0 .5 0 1-.3 1.5m21.9.7c-.2-.4-.5-.7-.5-1.4v-2.2c0-1 .1-1.8-.6-2.5-.6-.5-1.5-.7-2.2-.7-1.4 0-3 .5-3.3 2.2 0 .2.1.3.2.3l1.4.1c.2 0 .3-.1.3-.2.1-.6.6-.9 1.2-.9.3 0 .6.1.8.4.2.3.2.7.2 1v.2c-.9.1-2 .2-2.8.5a2.4 2.4 90 00-1.5 2.4c0 1.5.9 2.2 2.1 2.2 1.1 0 1.6-.2 2.4-1 .3.3.4.5.9.9a.3.3 90 00.3 0c.3-.3.8-.7 1.1-1 .2-.1.1-.2 0-.3m-18.9 2.2a25 25 90 0012.3 3.2 24.1 24.1 90 009.3-1.9c.7-.2 1 .3.5.7-2.8 1.9-6.6 3-10 3a18.3 18.3 90 01-12.4-4.6c-.3-.3-.1-.6.3-.4m24.5-.2c.3.3-.1 2.9-1.6 4.1-.2.2-.4.1-.3-.2.3-.8 1-2.6.7-3-.4-.5-2.3-.2-3.2-.1-.3 0-.3-.2-.1-.4 1.6-1.1 4.2-.8 4.5-.4m-8.1-4.5v.5c-.1.5-.1.9-.3 1.3-.2.4-.6.7-1 .7-.6 0-.9-.5-.9-1.1 0-1.2 1.1-1.4 2.2-1.4",
            3 /*Lenovo*/ => "m58 22v16.74h-50.76v-16.74m42.66 5.616c-1.944 0-3.51 1.458-3.51 3.348 0 1.89 1.512 3.348 3.51 3.348 1.998 0 3.564-1.404 3.51-3.348 0-1.89-1.512-3.348-3.51-3.348m-13.77 0c-1.944 0-3.51 1.458-3.51 3.348 0 1.89 1.512 3.348 3.51 3.348 1.944 0 3.51-1.404 3.51-3.348 0-1.89-1.512-3.348-3.51-3.348m-6.858 0c-.756 0-1.566.324-2.106 1.026v-.918h-1.782v6.426h1.836v-3.672c0-.648.54-1.35 1.512-1.35.702 0 1.512.54 1.512 1.35v3.672h1.782v-3.996c.054-1.458-1.026-2.592-2.646-2.592m-7.992 0c-1.944 0-3.456 1.404-3.456 3.348 0 1.944 1.512 3.348 3.672 3.348 1.188 0 2.43-.594 3.024-1.242l-1.08-.81c-.756.594-1.188.648-1.89.648a1.944 1.944 90 01-1.458-.54l4.536-1.89a3.618 3.618 90 00-.756-1.782c-.594-.702-1.512-1.026-2.592-1.026m20.034.108h-1.998l2.7 6.426h1.944l2.646-6.426h-1.998l-1.62 4.428m-29.376-6.318h-1.782v8.37h5.886v-1.62h-4.05v-6.75m36.126 3.294c1.026 0 1.782.81 1.728 1.782 0 1.026-.702 1.782-1.728 1.782-.972 0-1.728-.81-1.728-1.782 0-1.026.702-1.782 1.728-1.782m-13.77 0c1.026 0 1.782.81 1.728 1.782 0 1.026-.702 1.782-1.728 1.782-.972 0-1.728-.81-1.728-1.782 0-1.026.702-1.782 1.728-1.782m-14.688-.162c.594 0 1.134.378 1.404.918l-3.132 1.296c-.108-.594.108-1.134.324-1.512.27-.432.756-.648 1.404-.648",
        ];
                            
        foreach ($allData as $row){ 
            if (!in_array($row['manufacturer'], $manufacturers)) {
                $manufacturers[] = $row['manufacturer'];
                ?><div><svg><path d="<?echo $manufacturerSVG[$row['manufacturer']]?>"/></svg></div><?
            }
        }
                      ?></div>
                        <div id="hmcs-s" class="hmc-slider"><? 
        
        $devices = [];
        
        $typeDevice = [
            1 => "Mobile",
            2 => "Tablets",
            3 => "Streaming Devices",
            4 => "Wearables",
            5 => "Consoles",
            6 => "Accessories",
            7 => "VR Headsets",
        ];
                            
        foreach ($allData as $row){ 
            if (!in_array($row['type'], $devices)) {
                $devices[] = $row['type'];
                ?><a><?echo $typeDevice[$row['type']]?></a><?
            }
        }
                      ?></div>
                        <div id="hmc-i">
                            <a class="hmcnb">Sort by Price</a>
                            <a class="hmcnb">Sort by Condition</a>
                            <a class="hmcnb">Filter Available</a>
                            <aside>
                                <a id="hmci-e" class="hmcnb">
                                Export
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path d="M15 21H9C6.17157 21 4.75736 21 3.87868 20.1213C3 19.2426 3 17.8284 3 15M21 15C21 17.8284 21 19.2426 20.1213 20.1213C19.8215 20.4211 19.4594 20.6186 19 20.7487"/>
                                    <path d="M12 16V3M12 3L16 7.375M12 3L8 7.375"/>
                                </svg>
                            </a>
                            </aside>
                        </div>
                    </div>
                    <div id="hm-teller"><?
                    
        foreach ($allData as $row){ 
            
                      ?><div>
                            <!--div class="side-badge purp">HOT</div>
                            <div class="side-badge grn">NEW DEAL</div>
                            <div class="side-badge www">PRICE DROP</div-->
                            <div class="hmt-main">
                                <p><?echo $row['name']?> - <?echo $row['color']?> - <?echo $row['modelNumber']?></p>
                                <aside>
                                    <div>
                                        <div>
                                            <span>Warranty</span>
                                            <p>1 Year Manufacturer</p>
                                        </div>
                                        <div>
                                            <span>Packaging</span>
                                            <p>Retail Box</p>
                                        </div>
                                        <div>
                                            <span>Condition</span>
                                            <p>New Factory Sealed</p>
                                        </div>
                                    </div>
                                    <aside>
                                        <div>
                                            <span>ETA</span>
                                            <p><?echo $row['ETA']?> days</p>
                                        </div>
                                        <div>
                                            <span>Available</span>
                                            <p><?echo $row['stock']?></p>
                                        </div>
                                        <div>
                                            <span>Price</span>
                                            <p>$<?echo $row['price']?></p>
                                        </div>
                                        <div class="hmt-a" onclick="addItem(this)" data-index="<?echo enc($row['id'])?>" data-price="<?echo $row['price']?>" data-name="<?echo $row['name']?> - <?echo $row['color']?> - <?echo $row['modelNumber']?>">
                                            <?echo $row['added'] ? "Added to Cart" : "Add to Cart"?>
                                        </div>
                                    </aside>
                                </aside>
                            </div>
                        </div><?
                        
        }
        
                  ?></div>
                </div>
                <div id="hold-addToCart">
                    <div><?
                
        $uid = $_SESSION['uid'];
        $stmt = $conn->prepare("
            SELECT item.name, item.id, item.color, item.modelNumber, item.price
            FROM item 
            INNER JOIN (
                SELECT cart 
                FROM buyer 
                WHERE id = ?
            ) AS subquery 
            ON FIND_IN_SET(item.id, subquery.cart)
        ");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
              ?><div class="hmc-item">
                    <span><?echo $row['name']?> - <?echo $row['color']?> - <?echo $row['modelNumber']?></span>
                    <div>
                        <input name="hmci-qty" type="num" placeholder="1" data-index="<?echo enc($row['id'])?>">
                        <div>$<?echo $row['price']?></div>
                        <svg viewBox="-3.5 0 19 19" onclick="removeItem(this.parentNode)">
                            <path d="m11.4 13.6a1 1 0 01-1.5 1.5l-3.9-3.9-3.9 3.9a1 1 0 11-1.5-1.5l3.9-3.9-3.9-3.9a1 1 0 111.5-1.5l3.9 4 3.9-3.9a1 1 0 011.5 1.5l-3.9 3.8"/>
                        </svg>
                    </div>
                </div><?
            }
        }
        
        $stmt->close();
        $conn->close();
        
                  ?></div>
                    <button onclick="submitorder(this.parentNode)" type="submit">Submit Order</button>
                </div>
            </div>
        </div>
    </body>
    <script>
        function removeItem(a){
            let dataIndex = a.querySelector('input').getAttribute('data-index');
            fetch('#', {
                body:'removeItem='+encodeURIComponent(dataIndex), 
                method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}
            })
            .then(response => response.text())
            .then(data => {
                document.querySelector("#hm-teller div[data-index='"+dataIndex+"']").textContent = "Add to Cart";
                a.parentNode.remove();
            });
        }
        function submitorder(a){
            fetch('#', {
                body:'orderData='+encodeURIComponent(JSON.stringify(Array.from(a.querySelectorAll('.hmc-item>div>input')).map(b => ({index: b.getAttribute('data-index'), qty: b.value == "" ? 1 : b.value})))), 
                method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}
            })
            .then(response => response.text())
            .then(data => {
                console.log(data);
            });
        }
        function addItem(a){
            let dataIndex = a.getAttribute('data-index');
            fetch('#', {body:'itemIndex='+encodeURIComponent(dataIndex), method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}})
            .then(response => response.json())
            .then(data => {
                if(data){
                    a.textContent = "Added to Cart"
                    const newItem = document.createElement('div');
                    newItem.classList.add('hmc-item');
                
                    const spanElement = document.createElement('span');
                    spanElement.textContent = a.getAttribute('data-name');
                    
                    const inputElement = document.createElement('input');
                    inputElement.name = 'hmci-qty';
                    inputElement.type = 'num';
                    inputElement.placeholder = '1';
                    inputElement.dataset.index = dataIndex;
                    
                    const priceDiv = document.createElement('div');
                    priceDiv.textContent = '$'+a.getAttribute('data-price');
                    
                    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
                    svg.setAttribute("viewBox", "-3.5 0 19 19");
                    svg.setAttribute("onclick", "removeItem(this.parentNode)");
                
                    const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
                    path.setAttribute("d", "m11.4 13.6a1 1 0 01-1.5 1.5l-3.9-3.9-3.9 3.9a1 1 0 11-1.5-1.5l3.9-3.9-3.9-3.9a1 1 0 111.5-1.5l3.9 4 3.9-3.9a1 1 0 011.5 1.5l-3.9 3.8");
                    
                    svg.appendChild(path);
                    
                    const innerDiv = document.createElement('div');
                    innerDiv.appendChild(inputElement);
                    innerDiv.appendChild(priceDiv);
                    innerDiv.appendChild(svg);
                    
                    newItem.appendChild(spanElement);
                    newItem.appendChild(innerDiv);
                    
                    document.querySelector('#hold-addToCart > div').appendChild(newItem);
                }
            });
        }
    </script>
    <!--script>
        var a = [
            [0, ' ', ' ', '00', [' ', 'Â']], [1, '!', '!', '01', '!'], [2, '"', '"', '02', '"'], [3, '#', '#', '03', '#'],
            [4, '$', '$', '04', '$'], [5, '%', '%', '05', '%'], [6, '&', '&', '06', '&'], [7, "'", "'", "07", "'"],
            [8, '(', '(', '08', '('], [9, ')', ')', '09', ')'], [10, '*', '*', '10', '*'], [11, '+', '+', '11', '+'],
            [12, ',', ',', '12', ','], [13, '-', '-', '13', '-'], [14, '.', '.', '14', '.'], [15, '/', '/', '15', '/'],
            [16, '0', '0', '16', '0'], [17, '1', '1', '17', '1'], [18, '2', '2', '18', '2'], [19, '3', '3', '19', '3'],
            [20, '4', '4', '20', '4'], [21, '5', '5', '21', '5'], [22, '6', '6', '22', '6'], [23, '7', '7', '23', '7'],
            [24, '8', '8', '24', '8'], [25, '9', '9', '25', '9'], [26, ':', ':', '26', ':'], [27, ';', ';', '27', ';'],
            [28, '<', '<', '28', '<'], [29, '=', '=', '29', '='], [30, '>', '>', '30', '>'], [31, '?', '?', '31', '?'],
            [32, '@', '@', '32', '@'], [33, 'A', 'A', '33', 'A'], [34, 'B', 'B', '34', 'B'], [35, 'C', 'C', '35', 'C'],
            [36, 'D', 'D', '36', 'D'], [37, 'E', 'E', '37', 'E'], [38, 'F', 'F', '38', 'F'], [39, 'G', 'G', '39', 'G'],
            [40, 'H', 'H', '40', 'H'], [41, 'I', 'I', '41', 'I'], [42, 'J', 'J', '42', 'J'], [43, 'K', 'K', '43', 'K'],
            [44, 'L', 'L', '44', 'L'], [45, 'M', 'M', '45', 'M'], [46, 'N', 'N', '46', 'N'], [47, 'O', 'O', '47', 'O'],
            [48, 'P', 'P', '48', 'P'], [49, 'Q', 'Q', '49', 'Q'], [50, 'R', 'R', '50', 'R'], [51, 'S', 'S', '51', 'S'],
            [52, 'T', 'T', '52', 'T'], [53, 'U', 'U', '53', 'U'], [54, 'V', 'V', '54', 'V'], [55, 'W', 'W', '55', 'W'],
            [56, 'X', 'X', '56', 'X'], [57, 'Y', 'Y', '57', 'Y'], [58, 'Z', 'Z', '58', 'Z'], [59, '[', '[', '59', '['],
            [60, '\\', '\\', '60', '\\'], [61, ']', ']', '61', ']'], [62, '^', '^', '62', '^'], [63, '_', '_', '63', '_'],
            [64, 'NUL', '`', '64', '`'], [65, 'SOH', 'a', '65', 'a'], [66, 'STX', 'b', '66', 'b'], [67, 'ETX', 'c', '67', 'c'],
            [68, 'EOT', 'd', '68', 'd'], [69, 'ENQ', 'e', '69', 'e'], [70, 'ACK', 'f', '70', 'f'], [71, 'BEL', 'g', '71', 'g'],
            [72, 'BS', 'h', '72', 'h'], [73, 'HT', 'i', '73', 'i'], [74, 'LF', 'j', '74', 'j'], [75, 'VT', 'k', '75', 'k'],
            [76, 'FF', 'l', '76', 'l'], [77, 'CR', 'm', '77', 'm'], [78, 'SO', 'n', '78', 'n'], [79, 'SI', 'o', '79', 'o'],
            [80, 'DLE', 'p', '80', 'p'], [81, 'DC1', 'q', '81', 'q'], [82, 'DC2', 'r', '82', 'r'], [83, 'DC3', 's', '83', 's'],
            [84, 'DC4', 't', '84', 't'], [85, 'NAK', 'u', '85', 'u'], [86, 'SYN', 'v', '86', 'v'], [87, 'ETB', 'w', '87', 'w'],
            [88, 'CAN', 'x', '88', 'x'], [89, 'EM', 'y', '89', 'y'], [90, 'SUB', 'z', '90', 'z'], [91, 'ESC', '{', '91', '{'],
            [92, 'FS', '|', '92', '|'], [93, 'GS', '}', '93', '}'], [94, 'RS', '~', '94', '~'], [95, 'US', 'DEL', '95', 'Ã'],
            [96, 'FNC 3', 'FNC 3', '96', 'Ä'], [97, 'FNC 2', 'FNC 2', '97', 'Å'], [98, 'Shift B', 'Shift A', '98', 'Æ'],
            [99, 'Code C', 'Code C', '99', 'Ç'], [100, 'Code B', 'FNC 4', 'Code B', 'È'], [101, 'FNC 4', 'Code A', 'Code A', 'É'],
            [102, 'FNC 1', 'FNC 1', 'FNC 1', 'Ê'], [103, 'Start Code A', 'Start Code A', 'Start Code A', 'Ë'],
            [104, 'Start Code B', 'Start Code B', 'Start Code B', 'Ì'], [105, 'Start Code C', 'Start Code C', 'Start Code C', 'Í']
        ], b = 'Î';
    
        var CodeSymbol = (function() {
            function CodeSymbol(a,b,c,d,e,f) {
                Object.defineProperties(this, {
                    value:{value:a},
                    checksumValue:{value:b},
                    code:{value:c},
                    switchedCode:{value:d||c},
                    char:{value:e},
                    weight:{value:1-(f?0:a.length)},
                    isCtrl:{value:f},
                    isShif:{value:new Set(['Shift B','Shift A']).has(a)},
                    isSwitch:{value:!!d}
                });
            }
            CodeSymbol.prototype.toString = function(){return `<Code ${this.code.name}:${this.value}>`};
            return CodeSymbol;
        })();
        
        var CodeSet = (function() {
            function CodeSet(a,b,c) {
                Object.defineProperties(this, {
                    name:{value:a},
                    stopChar:{value:c}
                });
                this._data = b;
                this._values = new Map();
                this._symbols = new Map();
                this._byIndex = [];
                var d = {'A':1,'B':2,'C':3}[a];
                for(let i=0,l=b.length;i<l;i++){
                    this._values.set(b[i][d],b[i]);
                    this._byIndex[i]=b[i][d];
                }
            }
            var _p = CodeSet.prototype;
            _p.getByIndex = function(a){return this.get(this._byIndex[a])};
            Object.defineProperty(_p, 'switchSymbols', {
                get: function() {
                    return this._switchSymbols || (this._switchSymbols = ['Code A', 'Code B', 'Code C', 'Shift A', 'Shift B'].map(s => this.get(s)).filter(Boolean));
                }
            });
            _p.get = function(a) {
                if(!this._values.has(a)) return null;
                var b=this._symbols.get(a);
                if(!b){var d=this._values.get(a);var c=d[0];b = new CodeSymbol(a, c,this,{'Shift A':ca,'Start Code A':ca,'Code A':ca,'Shift B':cb,'Start Code B':cb,'Code B':cb,'Start Code C':cc,'Code C':cc}[a]||null,Array.isArray(d[d.length-1])?d[d.length-1][0]:d[d.length-1],(this.name=='A'&&c>=64)||(this.name=='B'&&c>=95)||(this.name=='C'&&c>=100));this._symbols.set(a, b)}
                return b};
            return CodeSet
        })();
    
        var ca=new CodeSet('A',a,b),cb=new CodeSet('B',a,b),cc=new CodeSet('C',a,b),sca=ca.get('Start Code A'),scb=cb.get('Start Code B'),scc=cc.get('Start Code C');
        function ns(a,b,c){let d=[],k=[],f,j;if(f = c.b.get(b.slice(c.value.length,c.value.length+(c.b==cc?2:1))))k.push(f);let g=`${c.b.name}::${c.value}`;let h=a.get(g)||new Set();a.set(g,h);if(!c.a.isSwitch) k.push(...c.b.switchSymbols);for(let i=0;i<k.length;i++){f = k[i];if(h.has(f))continue;h.add(f);j = c.as(f);if(j.value==b)return[j,0];d.push(j)}return[0,d]}
        function ap(a,b){var i,l,c,d;for(i=0,l=b.length;i<l;i++){c = b[i];d = a.get(c.weight);if(!d){d = [];a.set(c.weight,d)}d.push(c)}}
        function fs(a){var b=new Map(),c=ap(b,[new en([scb]),new en([sca]),new en([scc])]),i,l,d,e=new Map(),f,g,h,j,k;while(b.size){k=Array.from(b.keys());c=k[0];for(i=1,l=k.length;i<l;i++){if(k[i]<c)c=k[i]}if(c==0)break;d=b.get(c);b.delete(c);for(i=0,l=d.length;i<l;i++){g=ns(e,a,d[i]);if(g[0])return g[0];ap(b,g[1])}}return 0}
        function en(a,b,c){var d=b||a.map(function(s){return s.isCtrl?'':s.value}).join('');Object.defineProperties(this,{symbols:{value:a},value:{value:d},weight:{value:c||d.length+a.reduce(function(){},0)}})}
        var _p=en.prototype;
        _p.as=function(f){var l=Array.prototype.slice.call(this.symbols),m=this.value+(f.isCtrl?'':f.value),n=this.weight-this.value.length;l.push(f);return new en(l,m,m.length+n+f.weight)};
        Object.defineProperties(_p, {
            a:{get(){if(!this.symbols.length)throw new Error('There are no symbols yet');return this.symbols[this.symbols.length-1]}},
            b:{get(){const a=this.symbols;if(a.length>=3&&a[a.length-2].isShift)return a[a.length-2].code;return this.a.switchedCode}},
            c:{get(){var i,l,a,b=0;for(i=0,l=this.symbols.length;i<l;i++){a=i==0?1:i;b=b+(a*this.symbols[i].checksumValue)}if(!this._chars)this._chars=[...this.symbols.map(s => s.char),this.b.getByIndex(b%103).char, this.b.stopChar].join('');return this._chars}}
        });
        
        function encode(a) {
            var result = fs(a, false);
            return result && result.c || result;
        }
        
        let tenc = `355522573373002
358951616531549
355522574611129
355522573371329
355522574323840
358951616534600
355522571135346
358951616934842
355522575420645
355522575400480
355522571580707
355522574138040
355522574339762
355522574092502
358951616518827
358951616523926
358951613073487
355522575425289
358951613067703
355522573932021
355522574905240
355522570404586
358951617391166
355522575712041
358951617203965
358951616528701
355522573874009
355522573343989
355522574368407
355522571598006`.split('\n').map(id => encode(id));
console.log(tenc.join('\n'));
    </script-->
</html>