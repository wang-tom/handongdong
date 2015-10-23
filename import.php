<?php
header("Content-type:text/html;charset=utf-8");

include_once 'phpExcelIO.php';
$excelIO = new phpExcelIO();

@$fileInfo=$_FILES['myFile'];
if(isset($fileInfo)){
	$allowExt=array('txt','xls');
	$newName = $excelIO->uploadFile($fileInfo,'upload',false,$allowExt);
	
	$csv_arr = array();
	$result = array();
	if (file_exists($newName)){
		$csv_arr = $excelIO->excelIOManage($newName);
		
		foreach ($csv_arr as $key=>$value) {
			$createtime = time() + $key;
			$imgarr_color = $excelIO->get_color($csv_arr[$key]['desc']);
			$imgarray = $excelIO->get_imgarr($imgarr_color);
			$goods_spec = $excelIO->get_goodsdesc($imgarray, $createtime);
			var_dump($imgarray);
			$count_color = count($imgarray);
			if($count_color > 0){
				for ($i=0;$i<$count_color;$i++){
					$pro_desc = $excelIO->get_prodesc($imgarray,$createtime,$i);
					
				}
			}
			//var_dump($spec);
		}		
		
		//echo '<script>window.location.href="admin.php"</script>';
	}else{
		echo "upload error";
	}
}


//upload.html************************************************
<form action="upload_file" enctype="multipart/form-data" method="post">
	<input type="file" name="myFile"/>
	<input type="submit" value="upload"/>
</form>
