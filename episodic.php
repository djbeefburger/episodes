<?php

class Episodic{
 
  
  private 
    $_episodeCsvFile,$_templateFile,$_template,$_tileFileExtension,$_canWrite,$_tileIds,$_blockWrites,$_tags;


  public
    $volumes,$
  
  public function __construct($config){
    //read the csv that contains all episode data into an associative array with a single header row
    //row[]=array(row[0][0]=>row[1][0],row[0][1]=>row[1][1],etc)
    //unset empty fields? (need to see if this improves load times)
    //parse out volumes (seasons)
    //parse episode info 
    //parse out keywords for search table
    
    $this->_episodeCsvFile=(empty($config['episodeCsvFile'])?"episodes.csv":$config['episodeCsvFile']);
    $this->_templateFile=(empty($config['templateFile'])?"template.json":$config['templateFile']);
    return ($this->getTemplate() && $this->getEpisodesCsv());
    
  }

  public function getTemplate(){
    $tile = (array)json_decode(file_get_contents($this->_tileDataDir."/".$this->_tileFilenameBase.$id.".".$this->_tileFileExtension));
    return false;
  }

  public function getEpisodeCsv(){
    return false;
  }
  
  private function setTileDataDir($str){
    $this->_tileDataDir=$str;
	//die($this->_tileDataDir);
  }
  
  private function setTileFilenameBase($str="tile"){
    $this->_tileFilenameBase=$str;
  }
  
  private function setTileFileExtension($str="json"){
    $this->_tileFileExtension=$str;
  }
  
  private function setBlockWrites($boo){
    if($boo)$this->_blockWrites=true;
    else $this->_blockWrites=false;
  }
    
  private function getTileIdsFromDirectory(){
    //expect files with format "{$this->_tileFilenameBase}INT.{$this->_tileFileExtension}"
    $result=array();
    $files=scandir($this->_tileDataDir,1);
	//die($this->_tileDataDir.print_r( $files,true));
    if(!empty($files)){
       foreach($files as $k=>$v){
          $f2=explode(".{$this->_tileFileExtension}",str_replace($this->_tileFilenameBase,"",$v));
          if(!empty($f2)){
            $id_number=$f2[0];
            if(strlen($this->_tileFilenameBase . $id_number . ".".$this->_tileFileExtension )!=strlen($v)) unset($files[$k]); 
            else $files[$k]=$id_number;
          }
          else unset($files[$k]); 
       }
    }else $files=array();
    //read all filenames in $_tileDataDir, filter for files with FilenameBase, extract numeric id from filename, append to array 
	//die($this->_tileDataDir.print_r( $files,true));
    return array_values($files);
  }
      
  public function getTile($id){
    if(in_array($id,$this->_tileIds)){
		//die('FOUNDIT');
      $tile = (array)json_decode(file_get_contents($this->_tileDataDir."/".$this->_tileFilenameBase.$id.".".$this->_tileFileExtension));
      if(!empty($tile['tags'])){
        $tags=explode(',',$tile['tags']);
        foreach($tags as $tag)$this->_tags[$tag][$id]=1;
      }
      return $tile;
    }else return false;
  }
  
  public function delTile($id){
    if($this->_canWrite){
		$filename="{$this->_tileDataDir}/{$this->_tileFilenameBase}{$id}.{$this->_tileFileExtension}";
		//die($filename);
      $r=unlink($filename);
      $this->_tileIds=$this->getTileIdsFromDirectory();
      if($r)foreach($this->_tags as $t=>$i_array)if(isset($i_array[$id]))unset($this->_tags[$t][$id]);
      return $r;
    }else{
      return false;
    }
  }
      
  public function getTiles($tag=""){
    if(empty($tag)||empty($this->_tags[$tag])){
      $this->_tileIds=$this->getTileIdsFromDirectory();
      foreach($this->_tileIds as $id)$tiles[$id]=$this->getTile($id);
      foreach($tiles as $id=>$tile)if(empty($tiles[$id]))unset($tiles[$id]);
      return $tiles;
    }
    else{
      $filtered_ids=array_keys($this->_tags[$tag])
      foreach($filtered_ids as $id)$tiles[$id]=$this->getTile($id);
      foreach($tiles as $id=>$tile)if(empty($tiles[$id]))unset($tiles[$id]);
      return $tiles;
    }
  }
  
  public function writeTile($arr){
	//returns the new d or false
    if($this->_canWrite){
	  if(empty($arr['id']))$arr['id']=time();
	  $filepath="{$this->_tileDataDir}/{$this->_tileFilenameBase}{$arr['id']}.{$this->_tileFileExtension}";
      $r= file_put_contents($filepath,json_encode($arr));  
	  $this->_tileIds=$this->getTileIdsFromDirectory();
      return ($r?$arr['id']:false);
    }else{
      return false;
    }
  }
	
  public function getTags(){
    $this->getTiles();
    return $this->_tags;
  }

  
}


?>
