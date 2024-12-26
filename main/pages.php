<?
    $page = basename(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH));
    
    $pages = ['Dashboard'];
    
    if($_SESSION['role'] == "filler") $pages = ['Deals','Commits','Sales','Label'];
    if($_SESSION['role'] == "buyer") $pages = ['Order'];
    if($_SESSION['role'] == "staff") $pages = ['process','pos','i','users','payout'];
?>

<div id="sidebar">
    <a href="dashboard" class="<?echo $page=='dashboard'?'selected':''?>">Dashboard</a>
    <? if($_SESSION['role']=="filler"){ ?>
        <a href="deals" class="<?echo $page=='deals'?'selected':''?>">Deals</a>
        <a href="commits" class="<?echo $page=='commits'?'selected':''?>">Commits</a>
        <a href="sales" class="<?echo $page=='sales'?'selected':''?>">Sales</a>
        <a href="label" class="<?echo $page=='label'?'selected':''?>">Labels</a>
    <? }else if($_SESSION['role']=="buyer"){ ?>
        <a href="order" class="<?echo $page=='order'?'selected':''?>">Order</a>
    <? }else if($_SESSION['role']=="staff"){ ?>
        <a href="process" class="<?echo $page=='process'?'selected':''?>">Process</a>
        <a href="pos" class="<?echo $page=='pos'?'selected':''?>">Purchase Orders</a>
        <a href="i" class="<?echo $page=='inventory'?'selected':''?>">Inventory</a>
        <a href="users" class="<?echo $page=='users'?'selected':''?>">Users</a>
        <a href="payout" class="<?echo $page=='payout'?'selected':''?>">Payout</a>
    <? } ?>
</div>