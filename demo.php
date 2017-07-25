<?php
include_once("function.php");
include_once("crawler.php");
//指定经纬度(使用高德坐标)
$lat="22.727878";
$lng="114.227467";
//对经纬度base32编码
$Geohash=new Geohash();
$geohash=$Geohash->encode($lat,$lng);
//抓取外卖商户
$crawler=new crawler();
$ele_arr=$crawler->ele($geohash);
$meituan_arr=$crawler->meituan($lat,$lng,$geohash);
$baidu_arr=$crawler->baidu($lat,$lng,$geohash);
//对商户排序(方法有 up(升序),down(降序);参数可以使用 distance(距离),shop_name(商户名称))
$namesort=new Namesort();
$ele_arr=$namesort->up($ele_arr,"distance");
$meituan_arr=$namesort->up($meituan_arr,"distance");
$baidu_arr=$namesort->up($baidu_arr,"distance");
for($i=0;$i<20;$i++){
	echo $ele_arr[$i]["shop_name"]."\t".$ele_arr[$i]["take_out_cost"]."\t".$meituan_arr[$i]["shop_name"]."\t".$meituan_arr[$i]["take_out_cost"]."\t".$baidu_arr[$i]["shop_name"]."\t".$baidu_arr[$i]["take_out_cost"]."<br/>";
}
?>