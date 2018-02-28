<?php
/*
 |--------------------------------------------------------------------------
 | SkyCaiji (蓝天采集器)
 |--------------------------------------------------------------------------
 | Copyright (c) 2018 http://www.skycaiji.com All rights reserved.
 |--------------------------------------------------------------------------
 | 使用协议  http://www.skycaiji.com/licenses
 |--------------------------------------------------------------------------
 */

 namespace Admin\Event; use Think\Controller; use Admin\Controller\BaseController; use Admin\Model\CollectedModel; if(!defined('IN_SKYCAIJI')) { exit('NOT IN SKYCAIJI'); } class RcmsEvent extends ReleaseEvent{ public function setConfig($config){ $config['cms']=I('cms/a'); $config['cms_app']=I('cms_app/a'); if(empty($config['cms']['path'])){ $this->error('cms路径不能为空'); } if(empty($config['cms']['app'])){ $this->error('cms应用不能为空'); } if(empty($config['cms']['name'])){ $config['cms']['name']=$this->cms_name($config['cms']['path']); } $releCms=A('Release/'.ucfirst($config['cms']['app']),'Cms'); $releCms->init($config['cms']['path']); $releCms->runCheck($config['cms_app']); return $config; } public function export($collFieldsList,$options=null){ $releCmsClass='Release\\Cms\\'.ucfirst($this->config['cms']['app']).'Cms'; $releCms=new $releCmsClass(); $releCms->init(null,$this->release); $addedNum=0; foreach ($collFieldsList as $collFields){ $contUrl=$collFields['url']; $return=$releCms->runExport($collFields['fields']); if($return['id']>0){ $addedNum++; } $this->record_collected($contUrl,$return,$this->release); } $this->echo_msg('成功发布'.$addedNum.'条数据','green'); return $addedNum; } public function cms_name($cmsPath){ $cmsPath=realpath($cmsPath); if(empty($cmsPath)){ return ''; } static $cmsNames=array(); $md5Path=md5($cmsPath); if(!isset($cmsNames[$md5Path])){ $cmsName=''; $cmsFiles=$this->cms_files(); foreach ($cmsFiles as $cms=>$cmsFile){ $cmsFile=realpath($cmsPath.'/'.$cmsFile); if(!empty($cmsFile)&&file_exists($cmsFile)){ $cmsName=$cms; break; } } $cmsNames[$md5Path]=$cmsName; } return $cmsNames[$md5Path]; } public function cms_name_list($cmsPath,$return=false){ $cmsPath=realpath($cmsPath); static $list=array(); if($return){ foreach ($list as $cms=>$files){ $files=array_unique($files); $files=array_filter($files); $files=array_values($files); $list[$cms]=$files; } return empty($list)?array():$list; } if(!empty($cmsPath)){ $cmsName=$this->cms_name($cmsPath); if(!empty($cmsName)){ $list[$cmsName][]=$cmsPath; } } } public function cms_files(){ static $fiels=array ( 'discuz'=>'source/class/discuz/discuz_core.php', 'wordpress'=>'wp-includes/wp-db.php', 'dedecms'=>'include/dedetemplate.class.php', 'empirecms'=>'e/class/EmpireCMS_version.php', 'metinfo'=>'config/metinfo.inc.php', 'phpcms'=>'phpcms/base.php', ); return $fiels; } } ?>