<?php
class phpExcelIO{
	function uploadFile($fileInfo,$uploadPath = 'upload',$flag=true,$allowExt=array('txt','csv'),$maxSize = 2097152){
		if ($fileInfo ['error'] > 0) {
			switch ($fileInfo ['error']) {
				case 1 :
					$mes = '上传文件超过了PHP配置文件中upload_max_filesize选项的值'; break;
				case 2 :
					$mes = '超过了表单MAX_FILE_SIZE限制的大小'; break;
				case 3 :
					$mes = '文件部分被上传'; break;
				case 4 :
					$mes = '没有选择上传文件'; break;
				case 6 :
					$mes = '没有找到临时目录'; break;
				case 7 :
				case 8 :
					$mes = '系统错误'; break;
			}
			echo ( $mes );
			return false;
		}
		$ext = pathinfo ( $fileInfo ['name'], PATHINFO_EXTENSION );
		if(!is_array($allowExt)){
			exit('系统错误');
		}
		if (! in_array ( $ext, $allowExt )) {
			exit ( '非法文件类型' );
		}
		if ($fileInfo ['size'] > $maxSize) {
			exit ( '上传文件过大' );
		}
		if (! is_uploaded_file ( $fileInfo ['tmp_name'] )) {
			exit ( '文件不是通过HTTP POST方式上传上来的' );
		}
		$uniName = $fileInfo['name'];
		$destination = $uploadPath . '/' . $uniName;
		if (! @move_uploaded_file ( $fileInfo ['tmp_name'], $destination )) {
			exit ( '文件移动失败' );
		}
		return $destination;
	}
	/**
	 * 数据库连接
	 * Enter description here ...
	 */
	static function conn(){
		$conn = mysql_connect('127.0.0.1','root','root');
		mysql_query("set names utf8");
		mysql_select_db('b2b2c',$conn) or die("error".mysql_error());
	}
	/**
	 * csv数据包转化xls， 把数据拼装成二维数组
	 * Enter description here ...
	 * @param unknown_type $path
	 */
	function excelIOManage($path){
		require_once 'excel_reader2.php';
		$data = new Spreadsheet_Excel_Reader();
		$data->setOutputEncoding('UTF-8');
		$data->read($path);
		$excel = $data->sheets[0]['cells'];
		
		$result = array();
		$st = 3;
		for ($i = $st; $i <= $data->sheets[0]['numRows']; $i++) {
			for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
				$result[$i-$st] = array(
					'name' => $excel[$i][1],
					'cid' => $excel[$i][2],
					'price' => $excel[$i][3],
					'num' => $excel[$i][4],
					'desc' => addslashes($excel[$i][5]),
					'outer' => $excel[$i][6],
				);
			}
		}
		return $result;
	}
	/**
	 * 根据商品描述，获取颜色信息
	 * Enter description here ...
	 * @param unknown_type $str
	 */
    public function get_color($str){
		preg_match_all("/颜&nbsp;&nbsp;&nbsp;&nbsp;色(.*)&nbsp;&nbsp;&nbsp;&nbsp;/", $str, $arr);
	
		$str=strip_tags($arr['1']['0']);
		if(stripos($str,"尺")){
			$str = explode("尺&nbsp;&nbsp;&nbsp;&nbsp;码",$str);
		}
		$str_color = explode("|",$str[0]);
		return $str_color;
    }
    /**
     * 根据已存在的颜色信息，获取颜色对应spec_value_id，spec_image 拼装成数组(颜色和尺码组合
     * Enter description here ...
     * @param unknown_type $imgarr
     */
    public function get_imgarr($imgarr){
    	$result=array();
    	$color_arr = array();
    	for ($i=0;$i<count($imgarr);$i++){
    		$color_arr = phpExcelIO::get_SpecValues($imgarr[$i]);
    		$result[$i]['img_id'] = $color_arr['spec_value_id'];
    		$result[$i]['img_name']=$imgarr[$i];
    		$result[$i]['img_spec'] = $color_arr['spec_image'];
    		$result[$i]['img_spec_good']="";
    	}
    	return $result;
    }
    /**
     * 拼接goods数据表属性信息
     * Enter description here ...
     * @param unknown_type $imgarray
     * @param unknown_type $createtime
     */
    public function get_goodsdesc($imgarray,$createtime){
    	$result=array();
    	$goodsimgid=$createtime.'242';
    	$count = count($imgarray);
    	for($i=0;$i<$count;$i++){
    		$proctimgid=$createtime.$imgarray[$i]['img_id'];
    		$color_arr = phpExcelIO::get_SpecValues($imgarray[$i]['img_name']);
    		$result['1'][$proctimgid]=array(
    				'private_spec_value_id' => $proctimgid,
    				'spec_value' => $imgarray[$i]['img_name'],
    				'spec_value_id' => $color_arr['spec_value_id'],
    				'spec_image' => $color_arr['spec_image'],
    				"spec_goods_images"=>""
    				);
    	}
    	
    	$result['2']=array(
    		$goodsimgid=>array(
	    		"private_spec_value_id"=> $goodsimgid,
	    		"spec_value"=>"free",
	    		"spec_value_id"=> "242",
	    		"spec_goods_images"=>""
    			)
    	);
    	$result=serialize($result);
    	//var_dump($result);
    	return $result;
    }
    /**
     * 拼接products 描述信息
     * Enter description here ...
     * @param unknown_type $imgarray
     * @param unknown_type $createtime
     * @param unknown_type $i
     */
    public function get_prodesc($imgarray,$createtime,$i){
    	$goodsimgid=$createtime.'242';
	    $result=array(
	    	"spec_private_value_id"=>array(
		    	"1"=> $createtime.$imgarray[$i]['img_id'],
		    	"2"=> $goodsimgid
	    	),
	    	"spec_value"=>array(
		    	"1"=>$imgarray[$i]['img_name'],
		    	"2"=>"free"
	    	),
	    	"spec_value_id"=>array(
		    	"1"=>$imgarray[$i]['img_id'],
		    	"2"=>"242"
	    	)
    	);
    	$result=serialize($result);
    	//var_dump($result);
    	return $result;
    }
    /**
     * 根据颜色名称，获取spec_value_id，spec_value_id
     * Enter description here ...
     * @param unknown_type $value
     */
    private function get_SpecValues($value){
    	self::conn();
    	$spec_values = array();
    	$sql = "select spec_value_id, spec_image from b2c_spec_values where spec_value='$value' limit 1";
    	$result = mysql_query($sql);
    	while ($row = mysql_fetch_array($result)){
    		$spec_values = array(
    			'spec_value_id' => $row['spec_value_id'],
    			'spec_image' => $row['spec_image']
    		);
    	}
    	//var_dump($spec_values);
    	return $spec_values;
    }
    
	
}

