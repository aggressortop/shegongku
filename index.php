<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>社工库</title>
	<style>
		html,body{margin: 0px; padding: 0px;}
		body{background: #0F0F04; color: #6A9FA6;}
		#main{width: 600px; margin: 0px auto;}
		#unurl{width: calc(100% - 30px); height: 24px; background: #000000; color: #6A9FA6; padding: 5px; line-height: 24px; font-size: 18px; border: 1px solid #6A9FA6; border-radius: 3px; outline: none;}
		#but{width: 80px; height: 35px; margin: 15px; outline: none; border: 1px solid #6A9FA6; background: #000000; border-radius: 3px; color: #6A9FA6; font-size: 18px;}
		#but:hover{color: #FFF;}
	</style>
</head>
<script>
<!--
function check(form){
if(form.q.value==""){
  alert("不可为空！");
  form.q.focus();
  return false;
  }
}
-->
</script>
<body>
<?php
$time_start = microtime(true);
define('ROOT', dirname(__FILE__).'/');
define('MATCH_LENGTH', 0.1*1024*1024); //字符串长度 0.1M 自己设置，一般够了。
define('RESULT_LIMIT',100);
function my_scandir($path){//获取数据文件地址
        $filelist=array();
        if($handle=opendir($path)){
        while (($file=readdir($handle))!==false){
         if($file!="." && $file !=".."){
             if(is_dir($path."/".$file)){
                $filelist=array_merge($filelist,my_scandir($path."/".$file));
                 }else{
                  $filelist[]=$path."/".$file;
                 }
            }
        }
     }
    closedir($handle);
    return $filelist;
}
function get_results($keyword){//查询
    $return=array();
    $count=0;
    $datas=my_scandir(ROOT."shuju"); //数据库文档目录
    if(!empty($datas))foreach($datas as $filepath){
        $filename = basename($filepath);
        $start = 0;
        $fp = fopen($filepath, 'r');
          while(!feof($fp)){
                fseek($fp, $start);
                $content = fread($fp, MATCH_LENGTH);
                $content.=(feof($fp))?"\n":'';
                $content_length = strrpos($content, "\n");
                $content = substr($content, 0, $content_length);
                $start += $content_length;
                $end_pos = 0;
                while (($end_pos = strpos($content, $keyword, $end_pos)) !== false){
                    $start_pos = strrpos($content, "\n", -$content_length + $end_pos);
                    $start_pos = ($start_pos === false)?0:$start_pos;
                    $end_pos = strpos($content, "\n", $end_pos);
                    $end_pos=($end_pos===false)?$content_length:$end_pos;
                    $return[]=array(
                       'f'=>$filename,
                       't'=>trim(substr($content, $start_pos, $end_pos-$start_pos))
                         );
                    $count++;
                    if ($count >= RESULT_LIMIT) break;
                  }
                unset($content,$content_length,$start_pos,$end_pos);
                if ($count >= RESULT_LIMIT) break;
                  }
        fclose($fp);
       if ($count >= RESULT_LIMIT) break;
     }
     return $return;
}
if(!empty($_POST)&&!empty($_POST['q'])){
    set_time_limit(0);				//不限定脚本执行时间
    $q=strip_tags(trim($_POST['q']));
    $results=get_results($q);
    $count=count($results);
}
?>
<div id="main">
	<form name="from" action="index.php" method="post">
		<center><h1>社工库</h1>
		<input class="inurl"  id="unurl" name="q" placeholder="用户、电子邮件、QQ帐户,论坛账户…" value="<?php echo !empty($q)?$q:''; ?>" />
			<input onClick="check(form)" id="but" type="submit" value="查询" class="submit" />
		</center>
		<?php
       if(isset($count))
       {
         echo '<hr size="1" color="#333333">';
         echo '查询到 ' .$count .' 条数据,&nbsp;&nbsp;用时 ' . (microtime(true) - $time_start) . " 秒";
         if(!empty($results)){
         echo '<ol>';
         foreach($results as $v){
         echo '<li>来自['.$v['f'].']数据库 <br />内容: '.$v['t'].'</li><br />';
           }
		 echo '</ol>';
		 echo '<ul>';
         echo '<br /><br /><font color=#ffff00><li>数据来自互联网</li><br /><li>这里的信息不代表本站观点</li><br /><li>查询到乱码请更换查看编码以正确显示原始数据</li></font>';
         echo '</ul>';
            }
         echo '<hr align="center" width="550" color="#2F2F2F" size="1"><font color="#ff0000">本站不保证数据的准确性。';
         echo '<br />数据不是完整的？您想添加或删除它？';
         echo '<br />联系邮箱：lmfamk@qq.com</font>';
         echo '</ul>';
         }
?>
	</form>
</div>
</body>
</html>