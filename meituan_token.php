<script>
    var rohrdata = "";
    var Rohr_Opt = new Object;
    Rohr_Opt.Flag = 100009;
    Rohr_Opt.LogVal = "rohrdata";
</script>
<script src="rohr.js"></script>
<script>
var h = "?lat=<?php echo $_GET["lat"]?>&lng=<?php echo $_GET["lng"]?>&page_index=<?php echo $_GET["page_index"]?>&apage=<?php echo $_GET["apage"]?>";
document.write(Rohr_Opt.reload("http://i.waimai.meituan.com/ajax/v6/poi/filter" + h));
</script>