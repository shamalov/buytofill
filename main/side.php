<aside style="display:flex; flex-direction:column;">
    <header>
        <p><?$page=basename(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH));echo $page?></p>
        <input type="checkbox" id="t-SB"/>
        <label for="t-SB"><svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><rect ry="0.75" x="3" y="6.25"/><rect ry="0.75" x="3" y="11.25"/><rect ry="0.75" x="3" y="16.25"/></svg></label>
    </header>
    <div>
        <a href="dashboard" class="<?echo $page=='dashboard'?'onpage':''?>">Dashboard</a>
        <? if($_SESSION['role']=="filler"){ ?>
            <a href="deals" class="<?echo $page=='deals'?'onpage':''?>">Deals</a>
            <a href="commits" class="<?echo $page=='commits'?'onpage':''?>">Commits</a>
            <a href="sales" class="<?echo $page=='sales'?'onpage':''?>">Sales</a>
            <a href="labels" class="<?echo $page=='l'?'onpage':''?>">Labels</a>
        <? }else if($_SESSION['role']=="buyer"){ ?>
            <a href="order" class="<?echo $page=='order'?'onpage':''?>">Order</a>
        <? }else if($_SESSION['role']=="staff"){ ?>
            <a href="process" class="<?echo $page=='process'?'onpage':''?>">Process</a>
            <a href="po" class="<?echo $page=='pos'?'onpage':''?>">Purchase Orders</a>
            <a href="i" class="<?echo $page=='inventory'?'onpage':''?>">Inventory</a>
            <a href="users" class="<?echo $page=='users'?'onpage':''?>">Users</a>
            <a href="payout" class="<?echo $page=='payout'?'onpage':''?>">Payout</a>
        <? } ?>
    </div>
    <p style="margin-left: 1rem;">Shipping Instructions</p>
    <div style="margin: 1rem; padding: 1rem; border: 1px solid #333; height: fit-content; overflow: visible;">
        "Insert Name"<br>
        2207 Concord Pike<br>
        PMB 488, <?= N2A($_SESSION['uid'])?><br>
        Wilmington, DE 19803<br>
        <br>
        <span style="display:block;text-align:center">OR</span>
        <br>
        Ship to home<br>
        Request label<br>
        Ship to Us
    </div>
</aside>
