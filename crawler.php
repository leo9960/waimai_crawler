<?php
/*
*	外卖商户抓取（按照经纬度寻找附近的20个商户）
*
*/
class crawler{
	private function GET($url,$decode=true){
		$ch = curl_init($url);
		$headers = ['Content-Type: application/x-www-form-urlencoded; charset=utf-8',  'User-Agent:okhttp/3.2.0'];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		$output=curl_exec($ch);
		curl_close($ch);
		if($decode){
			return json_decode($output,true,512, JSON_BIGINT_AS_STRING);
		}else{
			return $output;
		}
	}
	private function POST($url,$post_fields,$cookies=[]){
		$ch = curl_init($url);
		$headers = ['Content-Type: application/x-www-form-urlencoded;',  'User-Agent:okhttp/3.2.0'];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_COOKIE,$cookies);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
		$output=curl_exec($ch);
		curl_close($ch);
		$obj=json_decode($output,true,512, JSON_BIGINT_AS_STRING);
		return $obj;
	}
	public function ele($geohash){
		$url='https://m.ele.me/restapi/v4/restaurants?type=geohash&geohash='.$geohash.'&offset=0&limit=20&extras[]=food_activity&extras[]=restaurant_activity';
		$content=$this->GET($url);
		$shop_arr=array();
		foreach($content as $index=>$shop){
			$logo_url = 'https://fuss10.elemecdn.com{url}?imageMogr/quality/75/format/jpg/thumbnail/!96x96r/gravity/Center/crop/96x96/';
			$native_url = 'eleme://restaurant?restaurant_id={id}&animation_type=1';
			$shop_arr[$index]=array();
			$shop_arr[$index]["source_img"]='./img/elm.png';
			$shop_arr[$index]["score"]=$shop["rating"];
			$shop_arr[$index]["month_sale_num"]=$shop["month_sales"];
			$shop_arr[$index]["shop_id"]="ELE".$shop["id"];
			$shop_arr[$index]["shop_name"]=$shop["name"];
			$shop_arr[$index]["native_url"]=str_replace('{id}',$shop["id"],$native_url);
			$shop_arr[$index]["deliver_time"]=$shop["order_lead_time"];
			$shop_arr[$index]["distance"]=$shop["distance"];
			$shop_arr[$index]["logo_url"]=str_replace('{url}',$shop["image_path"],$logo_url);
			$shop_arr[$index]["take_out_cost"]=$shop["delivery_fee"];
			$shop_arr[$index]["take_out_price"]=$shop["minimum_order_amount"];
			$shop_arr[$index]["welfare"][]="";
			foreach($shop["restaurant_activity"] as $index2=>$discount){
				if($discount["type"]==102){
					$discount_msg = $discount["description"];
					$shop_arr[$index]["welfare"][$index2]=$discount_msg;
				}
			}
		}
		return $shop_arr;
	}
	public function meituan($lat, $lng,$geohash){
		$page_index=0;
		$apage=1;
		$token_url=dirname('http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"]).'/meituan_token.php?lat='.$lat.'&lng='.$lng.'&page_index='.$page_index.'&apage='.$apage;
		$token=$this->GET($token_url,false);
		$url='http://i.waimai.meituan.com/ajax/v6/poi/filter?lat='.$lat.'&lng='.$lng.'&_token='.urlencode($token);
		$body = 'page_index=0&apage=1';
		$cookie = '_lxsdk_s=de7a943aaf190e7796a793b78465%7C%7C2; terminal=i; w_utmz="utm_campaign=(direct)&utm_source=5000&utm_medium=(none)&utm_content=(none)&utm_term=(none)"; w_visitid=; w_uuid=; utm_source=0; w_cid=; w_cpy_cn=""; w_cpy=; w_latlng='.($lat* 1000000).','.($lng* 1000000).'; w_geoid='.$geohash.';_lxsdk=15d75353e75c8-0b3279a2abed9-2249576f-38400-15d75353e75c8; webp=1;';
		$header=
		$content=$this->POST($url,$body,$cookie);
		$shop_arr=array();
		foreach($content["data"]["poilist"] as $index=>$shop){
			$native_url = 'meituanwaimai://waimai.meituan.com/menu?restaurant_id={id}&poiname=';
			$shop_arr[$index]=array();
			$shop_arr[$index]["source_img"]='./img/mt.png';
			$shop_arr[$index]["score"]=$shop["wm_poi_score"];
			$shop_arr[$index]["month_sale_num"]=$shop["month_sale_num"];
			$shop_arr[$index]["shop_id"]="MT_".$shop["id"];
			$shop_arr[$index]["shop_name"]=$shop["name"];
			$shop_arr[$index]["native_url"]=str_replace('{id}',$shop["id"],$native_url);
			$shop_arr[$index]["deliver_time"]=$shop["avg_delivery_time"];
			if(strstr($shop["distance"],"km")){
				$shop["distance"]=str_replace("km","",$shop["distance"]);
				$shop["distance"]=(string)((double)$shop["distance"]*1000);
			}
			if(strstr($shop["distance"],"m")){
				$shop["distance"]=str_replace("m","",$shop["distance"]);
			}
			$shop_arr[$index]["distance"]=$shop["distance"];
			$shop_arr[$index]["logo_url"]=$shop["pic_url_square"];
			$shop_arr[$index]["take_out_cost"]=$shop["shipping_fee"];
			$shop_arr[$index]["take_out_price"]=$shop["min_price"];
			$shop_arr[$index]["welfare"][]="";
			foreach($shop["discounts2"] as $index2=>$discount){
				if($discount["type"]==6){
					$discount_msg = $discount["info"];
					$shop_arr[$index]["welfare"][$index2]=$discount_msg;
				}
			}
		}
		return $shop_arr;
	}
	public function baidu($lat, $lng,$geohash){
		$url='http://api.map.baidu.com/geoconv/v1/?coords='.$lng.','.$lat.'&from=3&to=6&ak=ULEiGSyEImEElBdV0slGAHVn7bB6QmME';
		$content=$this->GET($url);
		$lat=(string)((double)$content["result"][0]["y"]);
		$lng=(string)((double)$content["result"][0]["x"]);
		$url='http://client.waimai.baidu.com/shopui/na/v1/cliententry?address=&return_type=paging&from=na-android&lng='.$lng.'&sv=3.9.1&lat='.$lat.'&loc_lat=0.0&loc_lng=0.0&cuid=E8E7DA5EEF8BD6A7BEE5918C36C96DDD|273344631523668';
		$content=$this->GET($url);
		$shop_arr=array();
		foreach($content["result"]["shop_info"] as $index=>$shop){
			$shop_arr[$index]=array();
			$shop_arr[$index]["source_img"]='./img/bd.png';
			$shop_arr[$index]["score"]=$shop["average_score"];
			$shop_arr[$index]["month_sale_num"]=$shop["saled_month"];
			$shop_arr[$index]["shop_id"]="BD".$shop["shop_id"];
			$shop_arr[$index]["shop_name"]=$shop["shop_name"];
			$shop_arr[$index]["native_url"]=$shop["bdwm_url"];
			$shop_arr[$index]["deliver_time"]=$shop["delivery_time"];
			$shop_arr[$index]["distance"]=$shop["distance"];
			$shop_arr[$index]["logo_url"]=$shop["logo_url"];
			$shop_arr[$index]["take_out_cost"]=$shop["takeout_cost"];
			$shop_arr[$index]["take_out_price"]=$shop["takeout_price"];
			$shop_arr[$index]["welfare"][]="";
			foreach($shop["welfare_act_info"] as $index2=>$discount){
				if($discount["type"]=='jian'){
					$discount_msg = $discount["msg"];
					$shop_arr[$index]["welfare"][$index2]=$discount_msg;
				}
			}
		}
		return $shop_arr;
	}
}
?>