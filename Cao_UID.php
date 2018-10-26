
<?php
error_reporting(0);
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '0');

//Lọc comment by Nguyen Huu Dat - J2TEAM Community.
//Hãy sử dụng code có văn hóa.

function getpage($link)
{
	$headers2 = array();

$headers2[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64; rv:44.0) Gecko/20100101 Firefox/44.0';
$headers2[] = 'Accept: application/json, text/javascript';
$headers2[] = 'Accept-Language: en-US,en;q=0.5';
$headers2[] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';

$c = curl_init();
curl_setopt($c, CURLOPT_URL, $link);
curl_setopt($c, CURLOPT_SSL_VERIFYPEER,false);
curl_setopt($c, CURLOPT_SSL_VERIFYHOST,false);
curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
curl_setopt($c, CURLOPT_HTTPHEADER, $headers2);
$page = curl_exec($c);
curl_close($c);

return $page;
}

function getpage2($data_post)
{
	global $token;
	$headers2 = array();

	$headers2[] = 'X-FB-SIM-HNI:45204';
	$headers2[] = 'X-ZERO-STATE:unknown';
	$headers2[] = 'X-FB-Net-HNI:45204';
	$headers2[] = 'Authorization:OAuth '.$token;
	$headers2[] = 'Host:graph.facebook.com';
	$headers2[] = 'X-FB-Connection-Type:WIFI';
	$headers2[] = 'User-Agent:[FBAN/FB4A;FBAV/161.0.0.35.93;FBBV/94117327;FBDM/{density=1.5,width=720,height=1280};FBLC/vi_VN;FBRV/94628452;FBCR/Viettel Telecom;FBMF/samsung;FBBD/samsung;FBPN/com.facebook.katana;FBDV/SM-N950W;FBSV/4.4.2;FBOP/1;FBCA/x86:armeabi-v7a;]';
	$headers2[] = 'Content-Type:application/x-www-form-urlencoded';
	$headers2[] = 'X-FB-Friendly-Name:FeedbackReactors';
	$headers2[] = 'X-FB-HTTP-Engine:Liger';
	$headers2[] = 'Connection:close';

	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, "https://graph.facebook.com/graphql");
	curl_setopt($c, CURLOPT_SSL_VERIFYPEER,false);
	curl_setopt($c, CURLOPT_SSL_VERIFYHOST,false);
	curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($c, CURLOPT_HTTPHEADER, $headers2);
	curl_setopt($c, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($c, CURLOPT_POSTFIELDS, $data_post);
	$page = curl_exec($c);
	curl_close($c);

	return $page;
}


function isInteger($input){
    return(ctype_digit(strval($input)));
}

function load($url,$page)
{
	echo "- Load page $page\n";
	//sleep(1);

	$page_content = json_decode(getpage($url));
	if (!empty($page_content->data))
	{
		$nextpage = $page_content->paging->next;
		$posts = $page_content->data;
		foreach($posts as $post)
		{
			$idpost = $post->id;
			$file = fopen("posts.txt","a+");
			fwrite($file,$idpost."\n");
			fclose($file);
		}

		if($nextpage != "")
		{
			load($nextpage,$page+1);
		}
	}
}

function load_comment($url,$page)
{
	global $uids;
	echo "- Load page comment $page\n";
	//sleep(1);

	$page_content = json_decode(getpage($url));
	if (!empty($page_content->data))
	{
		$nextpage = $page_content->paging->next;
		//lay comment cap 1
		$cmt1 = $page_content->data;
		foreach($cmt1 as $cmt)
		{
			$uid = $cmt->from->id;
			$name = $cmt->from->name;
			$uids[$uid] = $name;
			echo "- $uid \n";
			
			//cào uid comment có tag
			if(isset($cmt->message_tags)){
				//echo "- Load tag\n";
				foreach($cmt->message_tags as $tag){
					$uid = $tag->id;
					$name = $tag->name;
					$uids[$uid] = $name;
					echo "- $uid \n";
				}
			}
			
			//cào uid comment cap 2 (2000 limit)
			if(isset($cmt->comments)){
				//echo "- Load comment cap 2\n";
				foreach($cmt->comments->data as $cmt2){
					$uid = $cmt2->from->id;
					$name = $cmt2->from->name;
					$uids[$uid] = $name;
					echo "- $uid \n";
					
					//cào uid comment cap 2 có tag
					if(isset($cmt2->message_tags)){
						//echo "- Load tag cap 2\n";
						foreach($cmt2->message_tags as $tag2){
							$uid = $tag2->id;
							$name = $tag2->name;
							$uids[$uid] = $name;
							echo "- $uid \n";
						}
					}
				}
			}
		}

		if($nextpage != "")
		{
			load_comment($nextpage,$page+1);
		}
	}
}

function load_like($data_post,$page,$post_id)
{
	global $uids;
	echo "- Load page like $page like \n";
	
	$page_content = json_decode(getpage2($data_post));
	if (!empty($page_content->data->node->reactors))
	{
		$nextpage = $page_content->data->node->reactors->page_info->has_next_page;
		
		foreach($page_content->data->node->reactors->edges as $item)
		{
			$uid = $item->node->id;
			$name = $item->node->name;
			$uids[$uid] = $name;
			echo "- $uid \n";
		}

		if($nextpage == true)
		{
			$flag = $page_content->data->node->reactors->page_info->end_cursor;
			$data_post = 'doc_id=1566881810099752&method=post&locale=vi_VN&pretty=false&format=json&variables={"0":"'.$flag.'","2":"'.base64_encode("feedback:$post_id").'","3":1000}&fb_api_req_friendly_name=FeedbackReactors&fb_api_caller_class=graphservice';
			load_like($data_post,$page+1,$post_id);
		}
	}
}

//set vài mảng cần thiết
$uids = array();

//////////////////////////////////////////////////////////////////

$token = "EAAA....."; //Token fullquyen
$idpage = "1610321079217792"; //id của page muốn cào

/////////////////////////////////////////////////////////////////
//

$url = "https://graph.facebook.com/v2.8/$idpage/feed?limit=100&fields=id&access_token=$token";
load($url,1);



//cào like
foreach(file(__DIR__.'/posts.txt') as $line) {
	$line = str_replace("\n","",$line);
	$temp = null;
	$temp = explode("_",$line);
	$post_id = $temp[1];
	echo "=== Load like ".$post_id."\n";
	$data_post = 'doc_id=1566881810099752&method=post&locale=vi_VN&pretty=false&format=json&variables={"2":"'.base64_encode("feedback:$post_id").'","3":1000}&fb_api_req_friendly_name=FeedbackReactors&fb_api_caller_class=graphservice';
	load_like($data_post,1,$post_id);
}


//cào comment
foreach(file(__DIR__.'/posts.txt') as $line) {
	$line = str_replace("\n","",$line);
	$temp = null;
	$url = "https://graph.facebook.com/v2.8/$line/comments?fields=id,message_tags,from,comments.limit(2000){from,id,message_tags}&limit=1000&access_token=$token";
	load_comment($url,1);
}
echo "===> Tong UID cao duoc: ".count($uids)."\n";
 //lưu thành file
echo "- Luu thanh file \n";
foreach($uids as $key=>$value) {
	//ghi chi uid
	$file = fopen("uid.txt","a+");
	fwrite($file,$key."\n");
	fclose($file);
	
	//ghi chi uid va ten
	$file = fopen("uid_with_name.txt","a+");
	fwrite($file,$key." | ".$value."\n");
	fclose($file);
}


?>

	
quetbaiviet.php
Đang hiển thị quetbaiviet.php.