<?
    require('main/notifs.php');
    $page = basename(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH));
    
    if(isset($_GET['tutorial']) && $_GET['tutorial'] == true){
?>
    <script>sendNotif("Welcome to BuyToFill",200)</script>
<?
    }
?>
<style>
    *{
        box-sizing:border-box;
    }
    body{
        display:flex;
        color:white;
        background:#0b0b0f!important;
        > nav{
            min-width:55px;border-right: 2px solid #474bff50;height:101%!important;background:#15161b!important;border-top-right-radius:50%;border-bottom-right-radius:50%;transition:all .5s ease;display:flex;align-items:center;justify-content:center!important;flex-direction:column;
            &:before{content:'';position:absolute;width:2px;height:40%;top:30%;right:-2px;background:linear-gradient(transparent,#474bff,transparent);opacity: .5;transition:all .5s ease}
            &:hover{
                background: #15161b !important;border-top-right-radius:0;border-bottom-right-radius:0;
                &:before{opacity:1;height:100%;top:0;background:linear-gradient(transparent,#474bff 15%, #474bff 85%,transparent);}
            }
            a{
                position:relative;cursor:pointer;height:55px;transition:background .5s ease;
                p{
                    overflow:hidden;position:absolute;pointer-events:none;left: calc(100% + 2px);height:100%;display:flex;align-items:center;text-wrap:nowrap;
                    span{font-size:1.5rem;font-weight:bolder;margin-left:-100%;transition:margin.5s ease;background:-webkit-linear-gradient(-45deg, #ffffff33, #1d1e21);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
                }
                svg{
                    height:100%!important;width:100%!important;margin:0!important;padding:0 30%;box-sizing:border-box;transition:all .3s ease;
                    path{
                        stroke:#ffffff33!important;stroke-width:2px;stroke-linejoin:round;stroke-linecap:round;
                    }
                }
                &:hover{
                    background:#dddddd11;
                    p{pointer-events:all}
                    span{margin:0 .7rem}
                }
            }
            a:first-child{
                svg{fill:var(--green)!important}
                span{background:-webkit-linear-gradient(-45deg, var(--green),#1d1e21);-webkit-background-clip: text;-webkit-text-fill-color: transparent}
            }
            .selected{
                p{
                    left:calc(100% + 3px);
                    span{background:-webkit-linear-gradient(-45deg,var(--purple),#1d1e21);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
                }
                path{stroke:#474bff!important}
            }
        }
        > div{
            z-index: 10;
            transition:filter .3s ease;
            width: calc(100vw - 55px);
            padding: 0 7%;
            height:100%;
            overflow:scroll;
            display: flex;
            flex-direction: column;
            
            header{
                padding:3rem 0 2rem;
                display:flex;
                justify-content:space-between;
                border-bottom: 1px solid #252525;
                h5{
                    color: #aaa;
                    font-weight: 300;
                    font-size: 1rem;
                    padding-top: .4rem
                }
                div:last-child{
                    display:flex;
                    align-items:center;
                    gap:.8rem;
                    button{
                        box-shadow: 1px 1px 4px black;
                        cursor:pointer;
                        height: fit-content;
                        padding: .4rem 1rem;
                        border-radius: 100px;
                        border: 1px solid #464646;
                        background: #232128;
                        color:#ddd;
                    }
                }
            }
        }
        &:before{
            content:'';
            transition:width .3s ease;
            position:absolute;
            width:10%;
            height:150%;
            bottom:-25%;
            left:0;
            background:var(--purple);
            opacity:.3;
            filter:blur(100px);
        }
        &:has(nav:hover){
            > div{
                filter:blur(1px);
            }
            &:before{
                width:80%;
            }
        }
    }
</style>
<div class="preloader"><img src="main/favicon.ico"/></div>
<nav>
    <a href="/"><p><span>Home</span></p><svg viewBox="0 0 1e3 1e3"><path d="m100 305h775v-102c-4 3-8 6-11 10-8 7-15 14-23 21-16 15-32 31-48 46-10 9-19 18-29 26-16 15-31 30-47 45-9 8-19 17-28 26-16 15-32 29-47 44-10 9-19 17-29 26-16 15-31 30-47 45-10 8-19 17-29 26-15 15-31 29-46 44-10 9-19 17-29 26-16 15-31 30-47 45-10 8-19 17-29 26-15 15-31 29-46 44-10 9-20 18-29 27-16 14-31 29-47 44-10 9-20 18-29 27-16 14-31 28-46 42-10 10-20 19-30 28l-45 42c-6 6-12 11-18 17-30-28-59-56-90-84 6-5 11-10 16-14 18-18 37-35 55-53 9-8 19-17 28-26 16-15 32-29 47-44 10-9 19-18 29-26 16-15 31-30 47-45 10-9 19-17 28-26 16-14 32-29 47-44 10-9 19-18 29-26 16-15 31-30 47-45 10-9 19-17 28-26 16-14 32-29 47-44 10-9 19-17 29-26 16-15 31-30 47-45 10-9 19-18 29-27 15-14 31-28 46-43 10-9 19-18 29-27 16-14 31-29 47-44 10-8 19-17 29-26 15-15 31-29 46-44 9-8 19-17 29-26h-110v-118h326v304"></path><path d="m675 1001v-118h110l-10-10c-7-6-13-11-19-17-9-8-17-16-25-23-15-15-31-29-46-44-10-9-20-18-30-28-15-14-31-28-46-42-10-10-20-19-29-28-16-14-31-28-46-43-4-3-8-7-12-10l90-84c5 5 11 10 16 15 14 13 28 26 41 39l45 42c10 9 19 18 29 27l48 45c9 8 19 17 28 26 16 14 31 28 46 43 3 2 6 5 10 8v-102h126v304h-326"></path><path d="m1 148c2-1 4-2 6-4 5-4 10-9 16-14 13-13 27-26 41-39 8-8 17-16 26-24 3 3 6 5 9 8 8 7 16 15 24 23 17 15 33 30 49 46 10 9 19 17 29 26 15 14 30 29 45 43 9 8 19 17 28 25 16 16 33 31 49 47 10 9 19 17 29 26 15 14 30 29 45 43 3 3 6 5 9 8 3 2 2 3 0 5-15 15-31 29-47 44-7 7-14 13-21 19-6 6-12 12-19 18 0 0-1-1-2-1-13-13-26-26-40-38l-90-84c-10-10-20-19-31-29l-45-42c-10-10-20-19-29-28-16-14-31-28-46-43-10-9-20-18-30-28-1-1-3-2-5-2v-5"></path></svg></a>
    <a href="dashboard"><p><span>Dashboard</span></p><svg viewBox="0 0 24 24"><path d="M 4 10.284 C 4 9.445 4 9.026 4.106 8.638 C 4.2 8.294 4.354 7.97 4.562 7.68 C 4.796 7.353 5.122 7.088 5.773 6.56 L 8.973 3.96 C 10.053 3.082 10.592 2.644 11.191 2.476 C 11.72 2.328 12.28 2.328 12.809 2.476 C 13.409 2.644 13.948 3.082 15.027 3.959 L 18.227 6.559 C 18.878 7.089 19.204 7.353 19.438 7.679 C 19.646 7.969 19.8 8.294 19.894 8.638 C 20 9.026 20 9.445 20 10.284 L 20 15.2 C 20 16.88 20 17.72 19.673 18.362 C 19.385 18.926 18.926 19.385 18.362 19.672 C 17.72 20 16.88 20 15.2 20 L 8.8 20 C 7.12 20 6.28 20 5.638 19.673 C 5.074 19.385 4.615 18.926 4.327 18.362 C 4 17.72 4 16.88 4 15.2 L 4 10.285 Z"/></svg></a>
    <? if($_SESSION['role']=="filler"){ ?>
        <a href="deals" class="<?    echo $page=='deals'    ?'selected':''?>"><p><span>Deals</span></p><svg viewBox="0 0 24 24"><path d="M7.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/><path d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3z"/></svg></a>
        <a href="commits" class="<?  echo $page=='commits'  ?'selected':''?>"><p><span>Commits</span></p><svg viewBox="0 0 24 24"><path d="M7.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/><path d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3z"/></svg></a>
        <a href="sales" class="<?    echo $page=='sales'    ?'selected':''?>"><p><span>Sales</span></p><svg viewBox="0 0 24 24"><path d="M7.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/><path d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3z"/></svg></a>
    <? }else if($_SESSION['role']=="buyer"){ ?>
        <a href="order" class="<?    echo $page=='order'    ?'selected':''?>"><p><span>Order</span></p><svg viewBox="0 0 24 24"><path d="M7.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/><path d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3z"/></svg></a>
    <? }else if($_SESSION['role']=="staff"){ ?>
        <a href="process" class="<?  echo $page=='process'  ?'selected':''?>"><p><span>Process</span></p><svg viewBox="0 0 24 24"><path d="M7.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/><path d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3z"/></svg></a>
        <a href="i" class="<?      echo $page=='inv'      ?'selected':''?>"><p><span>Inventory</span></p><svg viewBox="0 0 24 24"><path d="M7.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/><path d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3z"/></svg></a>
        <!--a href="pos" class="<?      echo $page=='pos'      ?'selected':''?>"><p><span>Purchase Orders</span></p><svg viewBox="0 0 24 24"><path d="M7.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/><path d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3z"/></svg></a>
        <a href="users" class="<?    echo $page=='users'    ?'selected':''?>"><p><span>Users</span></p><svg viewBox="0 0 24 24"><path d="M7.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/><path d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3z"/></svg></a>
        <a href="payout" class="<?   echo $page=='payout'   ?'selected':''?>"><p><span>Payout</span></p><svg viewBox="0 0 24 24"><path d="M7.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/><path d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3z"/></svg></a-->
    <? } ?>
</nav>