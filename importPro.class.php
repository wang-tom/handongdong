<?php
class importPro{
	
	static function get_max_ColorP_order(){
		$data = M("B2cSpecValues","sdb_","EC")->field('max(p_order) as ord')->where("spec_id=1")->select();
		return $data['0']['ord'];
	}
	
	static function get_max_SizeP_order(){
		$data = M("B2cSpecValues","sdb_","EC")->field('max(p_order) as ord')->where("spec_id=2")->select();
		return $data['0']['ord'];
	}
	
	static function get_max_ColorSpecValueid(){
		$data = M("B2cSpecValues","sdb_","EC")->field('max(spec_value_id) as maxVal_id')->where("spec_id=1")->select();
		return $data['0']['maxVal_id'];
	}
	
	static function get_max_SizeSpecValueid(){
		$data = M("B2cSpecValues","sdb_","EC")->field('max(spec_value_id) as maxVal_id')->where("spec_id=2")->select();
		return $data['0']['maxVal_id'];
	}
	/**
	 * 根据xls 数据 获取颜色编号值(如果不存在 添加 并获取最大值
	 * @param unknown_type $value
	 */
	static function get_ColorSpecValue_id($value){
		/*$data = M("B2cSpecValues","sdb_","EC");
		$data->where("spec_value='$value' AND spec_id=1")->limit(1)->select();
		echo $data->getLastSql();*/
		$data = M("B2cSpecValues","sdb_","EC")->field('spec_value_id')->where("spec_value='$value' AND spec_id=1")->limit(1)->select();
		//输出添加颜色SQL语句
		//echo $data->getLastSql();
		if(count($data) > 0){
			return $data['0']['spec_value_id'];
		}else{
			$maxVal_id = self::get_max_ColorSpecValueid();
			$ord = self::get_max_ColorP_order();
			
			$spec_values= M("B2cSpecValues","sdb_","EC");
			$data['spec_value_id'] = $maxVal_id + 1;
			$data['spec_id'] = 1;
			$data['spec_value'] = $value;
			$data['p_order'] = $ord + 1;
			$spec_values->add($data);
			//输出添加颜色SQL语句
			$spec_values->getLastSql(); 
			
			return $maxVal_id + 1;
		}
		
	}
	
	static function get_ColorSpecValue($value){
		$data = M("B2cSpecValues","sdb_","EC")->field('spec_image')->where("spec_value='$value' AND spec_id=1")->select();
		return $data['0']['spec_image'];
	
	}
	/**
	 * 根据xls 数据 获取尺码编号值(如果不存在 添加 并获取最大值
	 * @param unknown_type $value
	 */
	static function get_SizeSpecValue_id($value){
		$data = M("B2cSpecValues","sdb_","EC")->field('spec_value_id')->where("spec_value='$value' AND spec_id=2")->limit(1)->select();
		//输出尺码SQL语句
		//echo $data->getLastSql();
		if(count($data) > 0){
			return $data['0']['spec_value_id'];
		}else{
			$maxVal_id = self::get_max_SizeSpecValueid();
			$ord = self::get_max_SizeP_order();
			
			$spec_values= M("B2cSpecValues","sdb_","EC");
			$data['spec_value_id'] = $maxVal_id + 1;
			$data['spec_id'] = 2;
			$data['spec_value'] = $value;
			$data['p_order'] = $ord + 1;
			$spec_values->add($data);
			//输出添加尺码SQL语句
			$spec_values->getLastSql(); 
			
			return $maxVal_id + 1;
		}
	}
	/**
	 * 添加products数据
	 * @param unknown_type $data
	 * @param unknown_type $rows
	 */
	static function add_csv_products($data,$props_pro,$props_goods,$auto='0'){
		$pro_param['seller_id'] = 41;
		$pro_param['price'] = $data['price'];
		$pro_param['store'] = $data['store'];
		$pro_param['name'] = $data['name'];
		$pro_param['color'] = $data['color'];
		$pro_param['size'] = $data['size'];
		$pro_param['bn'] = $data['bn']."_".$auto;
		$pro_param['times'] = time();
		
		$pro_param['spec_info'] = '颜色：'.$data['color'].'、尺码：'.$data['size'];
		
		//产品描述
		$pro_param['description'] = "http://api.handongdong.com/doc/20151020/".$pro_param['bn'].'.jpg';
		
		$pro_param['pro_paramOK'] = $props_pro;
		$pro_param['goods_paramOK'] = $props_goods;
		
		$products = M("UctProducts","csv_","BC");
		$products->add($pro_param);
		if($products == false) {
			showError("商品信息保存失败".M("UctProducts","csv_","BC")->getLastSql());
		}
	}
	/**
	 * 添加ecstore products数据
	 * @param unknown_type $data
	 * @param unknown_type $rows
	 */
	static function add_ecstore_b2c_products($data){
		$times = time();
		$pro_param['seller_id'] = 41;
		$pro_param['goods_id'] = $data['goods_id'];
		$pro_param['name'] = $data['name'];
		$pro_param['bn'] = $data['bn'];
		$pro_param['price'] = $data['price'];
		$pro_param["cost"] = $data["price"];
		$pro_param["mktprice"] = $data["price"];
		$pro_param['store'] = $data['store'];
		$pro_param['spec_desc'] = $data['pro_paramOK'];
		$pro_param['is_default'] = 'true';
		$pro_param["uptime"] = $times;
		$pro_param["last_modify"] = $times;
		$pro_param["disabled"] = 'false';
		$pro_param['marketable'] = 'true';
		
		//颜色：绿色、尺码：F
		$pro_param['spec_info'] = $data['spec_info'];
		
		$products = M("B2cProducts","sdb_","EC");
		$products->add($pro_param);
		echo $products->getLastSql();
		if($products == false) {
			showError("商品信息保存失败".M("B2cProducts","sdb_","EC")->getLastSql());
		}
	}
	/**
	 * 更新sdb_b2c_goods 数据表内容
	 * @param unknown_type $data
	 * @param unknown_type $imageParam
	 */
/*	static function add_ecstore_b2c_goods($data,$imageParam){
		$times = time();
		$goods_param['seller_id'] = 41;
		$goods_param['bn'] = $data['bn'];
		$goods_param['name'] = $data['name'];
		$goods_param['price'] = $data['price'];
		$goods_param['store'] = $data['store'];
		$goods_param["marketable"] = 'true';							//商品库存
		$goods_param["notify_num"] = 0;									//缺货登记
		$goods_param["uptime"] = $times;								//上架时间
		$goods_param["last_modify"] = $times;
		$goods_param["spec_desc"] = $data['goods_paramOK'];
		$goods_param["disabled"] = 'false';
		//$goods_param["image_default_id"] = $imageParam["image_id"];
		
		$goods_param["type_id"] = $data['type_id'];									//商品类型(参数不传，默认为:1,表示通用商品类型)
		$goods_param["cat_id"] = $data['cat_id'];
		$goods_param["nav_id"] = $data['nav_id'];	
		
		$goods = M("B2cGoods","sdb_","EC");
		$goods->add($goods_param);
		echo $goods->getLastSql();
		if($goods == false) {
			showError("商品信息保存失败".M("B2cGoods","sdb_","EC")->getLastSql());
		}
	}*/
	/**
	 * 导入产品时，插入数据到ecstore sdb_b2c_goods 占得 goods_id ******
	 * @param unknown_type $data
	 */
	static function add_ecstore_csv_goods($data,$imageParam){
		$times = time();
		$goods_param['seller_id'] = 41;
		$goods_param['bn'] = $data['bn'];
		$goods_param['name'] = $data['name'];
		$goods_param['price'] = $data['price'];
		$goods_param['store'] = $data['store'];
		$goods_param["marketable"] = 'true';							//商品库存
		$goods_param["notify_num"] = 0;									//缺货登记
		$goods_param["uptime"] = $times;								//上架时间
		$goods_param["last_modify"] = $times;
		$goods_param["disabled"] = 'false';
		$goods_param["image_default_id"] = $imageParam["image_id"];
		$goods_param["verify"] = '1';   //审核状态
		$goods_param["disabled"] = 'false';								//是否失效
		$goods_param["goods_type"] = "normal";							//销售类型
		$goods_param["p_order"] = 10;									//排序
		$goods_param["p_order"] = 10;							//动态排序
		$imagePath = "http://api.handongdong.com/doc/20151020/".$data['bn'].'.jpg';
		$goods_param['intro'] = '<div class="intro"><img src="'.$imagePath.'" /></div>';
		
		$goods = M("B2cGoods","sdb_","EC");
		$goods->add($goods_param);
		//echo $goods->getLastSql();
		if($goods == false) {
			showError("商品信息保存失败".M("B2cGoods","sdb_","EC")->getLastSql());
		}
	}
	/**
	 * 添加ecstore goods数据表数据
	 * @param unknown_type $data
	 */
	static function add_csv_goods($data){
		$goods_param['price'] = $data['price'];
		$goods_param['bn'] = $data['bn'];
		$goods_param['name'] = $data['name'];
		$goods_param['store'] = $data['store'];
		
		$goods = M("UctGoods","csv_","BC");
		$goods->add($goods_param);
		if($goods == false) {
			showError("商品信息保存失败".M("UctGoods","csv_","BC")->getLastSql());
		}
	}
	static function insertImage($bn){		
		$url = "/doc/20151020/";
		//$bn = $data["bn"];
		$imagePath = "http://api.handongdong.com".$url.$bn.'.jpg';
		$param["image_id"] = md5(time().rand(9999,99999));
		$param["storage"] = "filesystem";
		$param["image_name"] = getUrlName($imagePath);
		$param["ident"] = $url;
		
		$param["url"] = $imagePath;
		$param["l_ident"] = $url;
		$param["l_url"] = $imagePath;
		$param["m_ident"] = $url;
		$param["m_url"] = $imagePath;
		$param["s_ident"] = $url;
		$param["s_url"] = $imagePath;
		
		$param["width"] = 960;
		$param["height"] = 960;
		$param["watermark"] = 'false';
		$param["last_modified"] = time();
		
		$param["attach_id"] = M("ImageImage","sdb_","EC");
		$param["attach_id"]->add($param);
		
		if($param["attach_id"] == false) {
			showError("保存默认图片异常");
		}
		
		return $param;
	}
	/**
	 * 根据颜色 尺码 数组，拼装描述数组 并serialize 序列化
	 * @param string $color
	 * @param int $size
	 * @param string $xls_colorVal  [根据xls数据表内容，已逗号分割 获取多个颜色和尺码的值
	 * @param string $zxl_sizeVal
	 */
	static function get_csv_param_ok($color,$size,$xls_colorVal,$zxl_sizeVal){
		$times = time();
		$param_ok = array();
		$color_id = self::get_ColorSpecValue_id($color);
		$size_id = self::get_SizeSpecValue_id($size);
		//当前时间戳加上尺码编号
		$colorStamp = $times.$color_id;
		$sizeStamp = $times.$size_id;
		
		$param_ok['products'] = array(
			"spec_private_value_id" => array(
				"1" => "$colorStamp",
				"2" => "$sizeStamp"
			),
			"spec_value" => array(
				"1" => "$color",
				"2" => "$size"
			),
			"spec_value_id" => array(
				"1" => "$color_id",
				"2" => "$size_id"
			)
		);
		$goods_Cprops = array();
		$goods_Sprops = array();
		if(!empty($xls_colorVal)){
			$color_format = explode(",",$xls_colorVal);
			foreach ($color_format as $key=>$col_val){
				$colorID = self::get_ColorSpecValue_id($col_val);
				$goods_colStamp = $times.$colorID;
				$color_img = self::get_ColorSpecValue($col_val);
				
				$goods_Cprops[$goods_colStamp] = array(
					"private_spec_value_id" => "$goods_colStamp",
					"spec_value" => "$col_val",
	            	"spec_value_id" =>"$colorID",
					"spec_image" => "$color_img"
				);
			}
		}
		
		if(!empty($zxl_sizeVal)){
			$size_format = explode(",",$zxl_sizeVal);
			foreach ($size_format as $key=>$size_val){
				$sizeID = self::get_SizeSpecValue_id($size_val);
				$goods_sizeStamp = $times.$sizeID;
				
				$goods_Sprops[$goods_sizeStamp] = array(
					"private_spec_value_id" => "$goods_sizeStamp",
					"spec_value" => "$size_val",
	            	"spec_value_id" => "$sizeID"
				);
			}
		}
		if(!empty($goods_Cprops)){
			$param_ok['goods'] = array(
				"1" => $goods_Cprops,
				
				"2" => $goods_Sprops
			);
			
			$param_ok['goods_id'] = self::get_maxGoodsId();
		}
		
		return $param_ok;
	}
	
	/**
	 * 获取最大goods_id
	 * Enter description here ...
	 */
	static function get_maxGoodsId(){
		$data = M("B2cGoods","sdb_","EC")->field('max(goods_id) as maxVal_id')->select();
		return $data['0']['maxVal_id'] + 1;
	}
	/**
	 * 获取最大products_id
	 * Enter description here ...
	 */
	static function get_maxProductsId(){
		$data = M("B2cProducts","sdb_","EC")->field('max(goods_id) as maxVal_id')->select();
		return $data['0']['maxVal_id'] + 1;
	}
	
	/**
	 * 通过csv_products 数据表内容 导入数据到 ecstore 数据库
	 * int $pID
	 */
	static function get_CSVproducts_by_proID($pID){
		$data = M("UctProducts","csv_","BC")->where("id=".$pID)->select();
		return $data['0'];
	}

	static function get_ECgoodsID_by_bn($bn){
		$data = M("B2cGoods","sdb_","EC")->field('goods_id')->where("bn=".$bn)->limit(1)->select();
		return $data['0']['goods_id'];
	}
	
	static function update_UCTproducts($pID,$data){
		$model = M("UctProducts","csv_","BC");
		$model->where("id=".$pID)->save($data);
	}
}



