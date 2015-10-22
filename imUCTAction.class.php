<?php
class ImUCTAction extends Action{

	public function index(){
		//分类
		$classParam = M("B2cGoodsCat")->where("parent_id=0")->select();
		$this->assign("classParam",$classParam);
	
		//导航分类  韩国UCT seller_id=41
		$TaobaoSku = new TaobaoSku();
		$nav_param = $TaobaoSku->seller_nav_param(41);
		$this->assign("nav_param",$nav_param);

		$count = M("UctProducts","csv_","BC")->count();
		$count = $count > 1000 ? 1000 : $count;
		list($limit,$page) = parts($count,20,$_GET["p"]);
		$data = M("UctProducts","csv_","BC")->order("id DESC")->limit($limit)->select();
		$this->assign("page",$page);
		$this->assign("data",$data);
	
		$this->assign("import",$_GET["import"]);
		$this->display("ImportUCT:index");
	}
	
	public function getChildClass(){
		$did = checkNumber_notice($_GET["did"],"参数错误");
		$classParam = M("B2cGoodsCat")->where("parent_id=".$did)->select();
		foreach($classParam as $rows) {
			echo '<a href="javascript:;" did="'.$rows["cat_id"].'" tyid="'.$rows["type_id"].'">'.$rows["cat_name"].'</a>';
		}
		if($classParam == false) {
			echo '<div style="color:#CCC;text-align:center;line-height:100px;">无更多分类</div>';
		}
	}
	/**
	 * 绑定分类 type_id cat_id nav_id ************
	 */
	public function bindClass(){
		$proID = $_POST["proID"];
		$param["type_id"] = $_POST["type_id"];
		$param["cat_id"] = $_POST["cat_id"];
		$param["brand_id"] = $_POST["brand_id"];
		//导航分类序列化serialize
		$param["nav_id"] = serialize($_POST["nav_id"]);
		$model = M("UctProducts","csv_","BC");
		
		$model->where("id=".$proID)->save($param);
		$model->getLastSql();
		
	}
	
	public function getTypeClass(){
		$tyid = checkNumber_notice($_GET["tyid"],"参数错误");
		$this->objsql = M();
		$query=$this->objsql->query("select brand_id,brand_name from sdb_b2c_brand where 1 and brand_id in (select brand_id from sdb_b2c_type_brand where 1 and type_id='$tyid')");

		if(count($query)>0){
			foreach($query as $key=>$value){
				echo '<a href="javascript:;" bid="'.$value["brand_id"].'">'.$value["brand_name"].'</a>';
			}
		}else echo '<div style="color:#CCC;text-align:center;line-height:100px;">无更多品牌</div>';
		
	}
	
	function getGoodsCatName(){
		$cid = checkNumber_notice($_GET["cid"],"参数错误");
		$seller_id = 41;
		
		$sql = "SELECT cat_id,cat_name from 
				sdb_b2c_seller_goodscat where seller_id = $seller_id AND parent_id=$cid";
		$nav_parent = M("","","EC")->query($sql);
		foreach ($nav_parent as $value) {
			echo '<a href="javascript:;" did="'.$value['cat_id'].'">'.$value['cat_name'].'</a>&nbsp;&nbsp;';
		}
		//return $val;
	}
	function getThirthGoodsCatName(){
		$cid = checkNumber_notice($_GET["cid"],"参数错误");
		$seller_id = 41;
		
		$sql = "SELECT cat_id,cat_name from 
				sdb_b2c_seller_goodscat where seller_id = $seller_id AND parent_id=$cid";
		$nav_parent = M("","","EC")->query($sql);
		foreach ($nav_parent as $value) {
			echo '<a href="javascript:;" did="'.$value['cat_id'].'">'.$value['cat_name'].'</a>&nbsp;&nbsp;';
		}
	}
	
	/**
	 * 根据csv_uct_goods 表数据 产品名称获取goods_id 并且 更新csv_uct_products字段goods_id
	 * Enter description here ...
	 */
	function getGoodsid(){
		$bn = isset($_GET["bn"]) ? $_GET["bn"] : "";
		$pID = isset($_GET["pID"]) ? $_GET["pID"] : "";
		$importPro = new importPro();
		//插入goods数据表 image_default_id bn.jpg
		$goods_id = $importPro::get_ECgoodsID_by_bn($bn);
		echo $goods_id;
		
		$csv_pro = M("UctProducts","csv_","BC");
		$data['goods_id'] = $goods_id; 
		$csv_pro->where("id=".$pID)->save($data);
		//echo $csv_pro->getLastSql();
	}
	
	function importProduct(){
		$importPro = new importPro();
		$proID = $_POST["proID"];
		$data = array();
		foreach($proID as $id) {
			//$csv_proSql = "";
			$data = $importPro::get_CSVproducts_by_proID($id);
			
			//$csv_proSql ="update csv_uct_products set import_status='1' where id=$id";
			//mysql_query($csv_proSql);
			$uct_pro['import_status'] = "1";
			$importPro::update_UCTproducts($id, $uct_pro);
			//添加sdb_b2c_products 
			$importPro::add_ecstore_b2c_products($data);
		}
		//更新sdb_b2c_goods 数据表
		$EC_goods = M("B2cGoods","sdb_","EC");
		
/*		$goods_param["type_id"] = $data['type_id'];	
		$goods_param["cat_id"] = $data['cat_id'];
		$goods_param["nav_id"] = $data['nav_id'];	
		$goods_param["spec_desc"] = $data['goods_paramOK'];*/
		$type_id = $data['type_id'];
		$cat_id = $data['cat_id'];
		$brand_id = $data['brand_id'];
		$spec_desc = $data['goods_paramOK'];
		$nav_id = $data['nav_id'];
		$goods_id = $data['goods_id'];
		//echo $data['goods_id'];
		if(!empty($data['goods_id'])){
			$b2c_GoodsSql = "update sdb_b2c_goods set type_id='$type_id',cat_id='$cat_id',nav_id='$nav_id',brand_id='$brand_id',spec_desc='$spec_desc' where goods_id=$goods_id";
			mysql_query($b2c_GoodsSql);
			//echo $sql;
		}
		
		//$EC_goods->where("goods_id=".$data['goods_id'])->save($goods_param);
		
	}
	
	function readExcel(){
		header('Content-type:text/html; charset=utf8');
		
		require_once './doc/excel_reader2.php';
		$data = new Spreadsheet_Excel_Reader();
		
		$data->setOutputEncoding('gb2312');
		$data->read('./doc/1015.xls');
		//处理产品颜色和尺码********************
		$importPro = new importPro();
		
		$res = array();
		$excel = $data->sheets[0]['cells'];
		for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {
			$res[]= array(
				'id' => $excel[$i][1],
				'name' =>iconv("GB2312","UTF-8//IGNORE",$excel[$i][2]),
				'bn' => $excel[$i][3],
				'style' =>iconv("GB2312","UTF-8//IGNORE",$excel[$i][4]),
				'size' => $excel[$i][5],
				'color' => iconv("GB2312","UTF-8//IGNORE",$excel[$i][6]),
				'store' => $excel[$i][7],
				'price' => $excel[$i][10],
				'brand' => iconv("GB2312","UTF-8//IGNORE",$excel[$i][12]),
				'color_format' => iconv("GB2312","UTF-8//IGNORE",$excel[$i][14]),
				'size_format' => $excel[$i][15],
			);
		}
		$pro_props = array();

		foreach ($res as $key=>$value){
			$color_val = $value['color_format'];
			$size_val = $value['size_format'];
			
			$pro_props = $importPro::get_csv_param_ok($value['color'],$value['size'],$color_val,$size_val);
			$props_pro = serialize($pro_props['products']);
			$props_goods = serialize($pro_props['goods']);
			
			$auto = rand(00,99);
			//添加products csv_uct_products
			$importPro::add_csv_products($value, $props_pro, $props_goods,$auto);

			//添加products csv_uct_goods
			if (!empty($color_val)){
				//csv_goods 数据表信息 只做 参考 [ecstore sdb_b2c_goods****** 
				$importPro::add_csv_goods($value);
				
				//dump($imageParam);exit;
				//************导入数据到ecstore sdb_b2c_goods **********
				//ecstore goods 数据表
				$imageParam = $importPro::insertImage($value['bn']);
				$importPro::add_ecstore_csv_goods($value,$imageParam);
			}
			
		}
		
		echo '<hr>导入数据成功';
		//echo json_encode($res);
		//$this->assign('color',$color);
		//$this->display('ImportUCT:excel');
	}
	
	
}
