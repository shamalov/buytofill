<nav>
    <div style="cursor:pointer" onclick="document.querySelector('#profile').classList='open'">
        <div class="level" style="--client-l:<?echo $_SESSION['level']*20?>%"><div><?echo $_SESSION['level']?></div></div>
        <div style="display:flex;flex-direction:column">
            <span style="font-size:.6rem;font-weight:400;text-transform:uppercase">view <?echo $_SESSION['role']?> profile</span>
            <div class="user"><?echo $_SESSION['fn'].' '.$_SESSION['ln']?> <? require('N2A.php'); echo N2A($_SESSION['uid'])?></div>
        </div>
    </div>
    <svg viewBox="0 0 102 102" xmlns="http://www.w3.org/2000/svg" fill="#6CEBA5" height="30px" class="logo">
        <path d="m 85 32 c -2 -2 0 -6 0 -10 c -80 75 -70 75 -75 75 c -11 -5 -4 2 -9 -9 c 0 -5 0 5 75 -75 c -4 0 -9 2 -11 0 c -2 -2 0 -7 0 -11 c 2 -4 20 0 31 0 c 0 10 4 28 0 30 c -4 0 -9 2 -11 0"></path>
        <path d="m 67 100 c 0 -4 -2 -8 0 -10 c 2 -2 7 0 11 0 c -26 -31 -26 -24 -26 -27 c 0 -3 6 -9 9 -9 c 3 0 -4 0 26 26 c 0 -4 -2 -8 0 -10 c 2 -2 7 0 11 0 c 4 2 0 20 0 30 c -10 0 -29 4 -31 0"></path>
        <path d="m 3 13 c 5 -9 -1 -3 8 -8 c 2 0 -2 0 32 32 c -1 3 -5 7 -8 8 c -32 -34 -32 -30 -32 -32"></path>
    </svg>
    <!--div>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke='#fff' fill='none' stroke-width='2' stroke-linecap="round" stroke-linejoin="round"><path d="M0 0h24v24H0z"/><path d="M8 8a3.5 3 0 0 1 3.5 -3h1a3.5 3 0 0 1 3.5 3a3 3 0 0 1 -2 3a3 4 0 0 0 -2 4"/><path d="M12 19l0 .01"/></svg>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke='#fff' fill='none' stroke-width='2' stroke-linecap="round" stroke-linejoin="round"><path d="M0 0h24v24H0z"/><path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6"/><path d="M9 17v1a3 3 0 0 0 6 0v-1"/></svg>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke='#fff' fill='none' stroke-width='2' stroke-linecap="round" stroke-linejoin="round"><path d="M0 0h24v24H0z"/><path d="M19.875 6.27a2.225 2.225 0 0 1 1.125 1.948v7.284c0 .809 -.443 1.555 -1.158 1.948l-6.75 4.27a2.269 2.269 0 0 1 -2.184 0l-6.75 -4.27a2.225 2.225 0 0 1 -1.158 -1.948v-7.285c0 -.809 .443 -1.554 1.158 -1.947l6.75 -3.98a2.33 2.33 0 0 1 2.25 0l6.75 3.98h-.033z"/><path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/></svg>
    </div-->
</nav>
<style>
    div:has(>#profile){content:'';background:#000000aa;pointer-events:none;width:100vw;height:100vh;position:absolute;z-index:99998;top:0;left:0;opacity:0;transition:opacity .5s ease}
    div:has(>.open){opacity:1!important;pointer-events:auto!important}
    #profile{width:70vw;height:70vh;position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);padding:.5rem;background:var(--semi);border:1px solid var(--purple);border-radius:5px;display:flex;gap:.5rem;transition:width .5s ease}
    .open{width:60vw!important}
    #profile>div,#profile ul{border-radius:.3rem;box-sizing:border-box;border:1px solid var(--green-tint);background:var(--mid);padding:.5rem}
    #profile>ul{list-style-type:none;width:fit-content}
    #profile li{cursor:pointer;text-wrap:nowrap;border-radius:.25rem;font-weight:500;font-size:.9rem;padding:.5rem 3.5rem .5rem .8rem;color:#888}
    #profile li:not(:last-child){margin-bottom:.1rem}
    #profile li:hover{background:var(--tint);color:#aaa}
    #profile ul span{color:#777;font-size:.7rem;display:block;font-weight:700;padding:.2rem .8rem .4rem .8rem}
    #profile ul hr{margin:.5em;border-color:var(--semi)}
    #profile>div{position:relative;width:100%}
    #closeProfile{right:.65rem;top:.65rem;width:2rem;height:2rem;border-radius:2px;display:flex;justify-content:center;cursor:pointer;align-items:center;position:absolute;background:var(--mid);color:var(--green-tint);border:1px solid}
    #closeProfile:hover{color:var(--green)}
    #profile>div:before{content:'';background:var(--semi);right:.4rem;width:2.5rem;height:2.5rem;top:.4rem;border:1px solid var(--green-tint);border-radius:.25rem;position:absolute}
    #profile input,select{background:var(--semi);border:1px solid var(--green-tint);border-radius:2px;appearance:none;color:#aaa;outline:0;padding:.5rem;margin-bottom:-.4rem}
    #profile label{margin-bottom:-.6rem}
    #profile div>button{position:absolute;right:-1rem;color:#999;font-size:1.2rem;top:70%;border:0;background:transparent;transform:translate(-50%, -50%)}
    #profile div>button>svg{width:24px;height:24px;cursor:pointer;outline:0}
    #profile div:has(>button)>input{width:100%;box-sizing:border-box}
    #profile>div>div:has(>input[type="text"])>button>svg>path{opacity:.5}
    #profile button[type="submit"]{background:var(--green-bg);border:1px solid;padding:.5rem;cursor:pointer;border-radius:4px;color:var(--green)}
    #profile button[type="submit"]:hover{background:var(--green);color:black}
</style>
<div>
    <div id="profile">
        <ul>
            <span>USER SETTINGS</span>
            <li>My Account</li>
            <hr>
            <li>Payment Details</li>
            <hr>
            <li>Appearance</li>
            <li>Notifications</li>
            <li>Language</li>
            <hr>
            <li>Log Out</li>
        </ul>
        <div>
            <div id="closeProfile" onclick="this.parentNode.parentNode.classList=''">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" width="24"><path d="M0 0h24v24H0z" stroke="none" fill="none"></path><path d="M18 6l-12 12"></path><path d="M6 6l12 12"></path></svg>
            </div>
            <?if($_SESSION['role'] == "filler"){?>
            <form style="display:flex;flex-direction:column;height:100%;justify-content:space-between" onsubmit="handleDetails(this,event)">
                <div style="display:flex;flex-direction:column;gap:1rem;color:#888;padding-bottom: 2rem;overflow-y:auto;">
                    <?  
                        $uid = $_SESSION['uid'];
                        $email = $_SESSION['email'];
                        $conn = new mysqli(getenv('DATABASE_HOST'), getenv('DATABASE_USER'), getenv('DATABASE_PASS'), getenv('DATABASE_NAME'));
                        $stmt = $conn->prepare("SELECT ahn,ban,rn,bat,address,state,city,zip FROM filler WHERE id = ?");
                        $stmt->bind_param("i", $uid);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        while ($row = $result->fetch_assoc()){
                    ?>
                        <label for="ahn">Account Holder Name</label>
                        <input id="ahn" value="<? if(isset($row['ahn'])){ echo $row['ahn']; } ?>" placeholder="John Doe" type="text" minlength="2" maxlength="50" autocomplete="off" required>
                        <label for="ban" >Bank Account Number</label>
                        <div style="position: relative;">
                            <input id="ban" value="<? if(isset($row['ban'])){ echo $row['ban']; } ?>" type="password" placeholder="123456789" minlength="8" maxlength="17" autocomplete="new-password" required>
                            <button onclick="managepwd(this, event)">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none">
                                    <path stroke="none" d="M0 0h24v24H0z"/>
                                    <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                    <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                                </svg>
                            </button>
                        </div>
                        <label for="rn" >Routing Number</label>
                        <div style="position: relative;">
                            <input id="rn" value="<? if(isset($row['rn'])){ echo $row['rn']; } ?>" type="password" minlength="9" maxlength="9" placeholder="123456789" autocomplete="new-password" required>
                            <button onclick="managepwd(this, event)">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none">
                                    <path stroke="none" d="M0 0h24v24H0z"/>
                                    <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/>
                                    <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/>
                                </svg>
                            </button>
                        </div>
                        <label for="bat">Bank Account Type</label>
                        <select id="bat" required>
                            <option <?php echo (!isset($row['bat'])) ? 'selected' : ''; ?>></option>
                            <option value="b" <?php echo (isset($row['bat']) && $row['bat'] == 0) ? 'selected' : ''; ?>>Business</option>
                            <option value="p" <?php echo (isset($row['bat']) && $row['bat'] == 1) ? 'selected' : ''; ?>>Personal</option>
                        </select>
                        <label for="add">Address</label>
                        <input id="add" value="<? if(isset($row['address'])){ echo $row['address']; } ?>" placeholder="5 Inter Cir, Apt 201" type="text" minlength="2" maxlength="50" autocomplete="off" required>
                        <label for="sta">State</label>
                        <input id="sta" value="<? if(isset($row['state'])){ echo $row['state']; } ?>" placeholder="NY" type="text" minlength="2" maxlength="2" autocomplete="off" required>
                        <label for="cit">City</label>
                        <input id="cit" value="<? if(isset($row['city'])){ echo $row['city']; } ?>" placeholder="New Hyde Park" type="text" minlength="2" maxlength="50" autocomplete="off" required>
                        <label for="zip">ZIP Code</label>
                        <input id="zip" value="<? if(isset($row['zip'])){ echo $row['zip']; } ?>" placeholder="11040" type="text" minlength="5" maxlength="5" autocomplete="off" required>
                    <?  
                        }
                        $stmt->close();
                        $conn->close();
                    ?>
                </div>
                <button type="submit">Confirm Payment Details</button>
            </form>
            <?}?>
        </div>
    </div>
</div>
<script>
    function managepwd(a,e){
        e.preventDefault();
        let input = a.parentNode.querySelector('input')
        if(input.getAttribute('type') == "password") input.type = "text"
        else input.type = "password"
    }
    function handleDetails(a, e) {
        e.preventDefault();
        fetch('dashboard', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: "paymentDetailsInput="+[...a.querySelectorAll('input')].map(input=>input.value)+"&paymentDetailsSelect="+a.querySelector('select').value
        })
        .then(response => response.json())
        .then(data => {
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
</script>