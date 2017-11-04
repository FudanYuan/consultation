<?php 
/**
 * Excel 模型
 * Author yzs
 * CreateTime 2017/8/16
 */
namespace app\model;

class Excel{
	public function __construct(){
		require_once (ADDON_PATH.'Sdk/phpexcel/PHPExcel.php');
 	}

    /**
     * 数组转xls格式的excel文件
     * @param  array  $data      需要生成excel文件的数组
     * @param  string $filename  生成的excel文件名
     * @param  string $template  生成的excel文件名
     *      示例数据：
            $data = array(
            array(NULL, 2010, 2011, 2012),
            array('Q1',   12,   15,   21),
            array('Q2',   56,   73,   86),
            array('Q3',   52,   61,   69),
            array('Q4',   30,   32,    0),
            );
     */
    function export($data,$filename='simple.xls', $template=''){
        ini_set('max_execution_time', '0');
        //Vendor('PHPExcel.PHPExcel');
        $filename=str_replace('.xls', '', $filename).'.xls';
        if($template){
            $phpexcel = \PHPExcel_IOFactory::load($template);     //加载excel文件,设置模板
        } else{
            $phpexcel = new \PHPExcel();
        }
        $phpexcel->getProperties()
            ->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");
        $phpexcel->getActiveSheet()->fromArray($data);
        $phpexcel->getActiveSheet()->setTitle('Sheet1');
        $phpexcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=$filename");
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0
        $objwriter = \PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
        $objwriter->save('php://output');
        exit;
    }

	/**
	 * 导入excel文件
	 * @param  string $params excel文件路径
	 * @return array        excel文件内容数组
	 */
	public function import($params){
	    $ret = ['errors' => [], 'data' => []];
        $errors = $this->filterField($params);
        if(!empty($errors)){
            $ret['errors'] = $errors;
            return $ret;
        }
        else{
            $file = '.'.$params['file'];
        }
		// 判断文件是什么格式
		$type = pathinfo($file);
		$type = strtolower($type["extension"]);
		//$type=$type==='csv' ? $type : 'Excel5';
		ini_set('max_execution_time', '0');
		$phpexcel = new \PHPExcel();
		// 判断使用哪种格式
		//$objReader = \PHPExcel_IOFactory::createReader($type); 
		if( $type =='xlsx'){
			$objReader = new \PHPExcel_Reader_Excel2007();
		}else{
			$objReader = new \PHPExcel_Reader_Excel5();
		}
		$objPHPExcel = $objReader->load($file);
		$sheet = $objPHPExcel->getSheet(0);
		// 取得总行数
		$highestRow = $sheet->getHighestRow();
		// 取得总列数
		$highestColumn = $sheet->getHighestColumn();
//		//循环读取excel文件,读取一条,插入一条
//		$data=array();
//		//从第一行开始读取数据
//		for($j=1;$j<=$highestRow;$j++){
//			//从A列读取数据
//            $temp = [];
//			for($k=1;$k<=$highestColumn;$k++){
//				// 读取单元格
//				$cell = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($k, $j)->getValue();
//                array_push($temp, $cell);
//			}
//			array_push($data, $temp);
//		}
        $num = 0;
        $data = [];
        $start = 1;
        $keyRow = '';
        $cols = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        for ($col = 0; $col < $cols; $col++){
            $k = \PHPExcel_Cell::stringFromColumnIndex($col);;
            $keyRow .= $objPHPExcel->getActiveSheet()->getCell("$k$start")->getValue() . '\\';//读取单元格
        }
        $keys = explode("\\", $keyRow);
        array_pop($keys);

        for($j=2;$j<=$highestRow;$j++) {
            $rowData = '';
            for ($col = 0; $col < $cols; $col++){
                $k = \PHPExcel_Cell::stringFromColumnIndex($col);
                $rowData .= $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue() . '\\';//读取单元格
            }
            $rowDatas = explode("\\", $rowData);
            array_pop($rowDatas);
            $add_data = [];
            foreach($rowDatas as $k => $value){
                $add_data[$keys[$k]] = $value;
            }
            $data[]=$add_data;
            $num++;
        }
        $ret['keys'] = $keys;
        $ret['data'] = $data;
        return $ret;
	}

    /**
     * 创建(导出)Excel数据表格
     * @param $list
     * @param $filename 导出的Excel表格数据表的文件名
     * @param $indexKey $list数组中与Excel表格表头$header中每个项目对应的字段的名字(key值)
     * @param int $startRow 第一条数据在Excel表格中起始行
     * @param bool $excel2007 是否生成Excel2007(.xlsx)以上兼容的数据表
     * @return bool
     * 比如: $indexKey与$list数组对应关系如下:
     *    $indexKey = array('id','username','sex','age');
     *    $list = array(array('id'=>1,'username'=>'YQJ','sex'=>'男','age'=>24));
     */
    public function exportExcel($list,$filename, $template, $indexKey,$startRow=1,$excel2007=false){
        if(empty($filename)) $filename = time();
        if(!is_array($indexKey)) return false;

        $header_arr = array('A','B','C','D','E','F','G','H','I','J','K','L','M', 'N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        //初始化PHPExcel()
        $objPHPExcel = new \PHPExcel();

        //设置保存版本格式
        if($excel2007){
            $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
            $filename = $filename.'.xlsx';
        }else{
            $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
            $filename = $filename.'.xls';
        }

        // 写数据到表格里
        $objActSheet = $objPHPExcel->getActiveSheet();
        foreach ($indexKey as $index => $key){
            $objActSheet->setCellValue($key);
        }
        foreach ($list as $row) {
            foreach ($indexKey as $key => $value){
                // 这里是设置单元格的内容
                $objActSheet->setCellValue($header_arr[$key].$startRow,$row[$value]);
            }
            $startRow++;
        }

        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=$filename");
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0
        $objwriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objwriter->save('php://output');
        exit;
    }


    /**
     * excel -> 数组
     * @param string $filename
     * @return array
     */
    public function excelToArray($filename){
        require_once (ADDON_PATH.'Sdk/phpexcel/PHPExcel/IOFactory.php');
        $objPHPExcelReader = \PHPExcel_IOFactory::load($filename);
        $reader = $objPHPExcelReader->getWorksheetIterator();

        //循环读取sheet
        foreach($reader as $sheet) {
            mydump($sheet);
            //读取表内容
            $content = $sheet->getRowIterator();
            //逐行处理
            $res_arr = array();
            foreach($content as $key => $items) {
                $rows = $items->getRowIndex();    			//行
                $columns = $items->getCellIterator();		//列
                $row_arr = array();
                //确定从哪一行开始读取
                if($rows < 2){
                    continue;
                }
                //逐列读取
                foreach($columns as $head => $cell) {
                    //获取cell中数据
                    $data = $cell->getValue();
                    $row_arr[] = $data;
                }
                $res_arr[] = $row_arr;
            }

        }
        return $res_arr;
    }

    /**
     * 过滤导入信息
     * @param $data
     * @return array
     */
    private function filterField($data){
        $errors = [];
        if (isset($data['file']) && !$data['file']){
        $errors['file'] = '导入文件不能为空';
        }
        return $errors;
    }

}
?>