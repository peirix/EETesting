<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mithra62 - Backup Pro
 *
 * @package		mithra62:m62_backup
 * @author		Eric Lamb
 * @copyright	Copyright (c) 2011, mithra62, Eric Lamb.
 * @link		http://mithra62.com/projects/view/backup-pro/
 * @version		1.8.2
 * @filesource 	./system/expressionengine/third_party/m62_backup/
 */

/**
 * Add the factory for the DB
 */
include_once('DB/class.factory.DB.php') ;

/**
 * Give us M62_Pclzip
 */
include_once 'pclzip.lib.php';

 /**
 * Backup Pro - SQL Backup Library
 *
 * SQL Backup Library
 *
 * @package 	mithra62:m62_backup
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/m62_backup/libraries/M62_sql_backup.php
 */
class M62_sql_backup
{
    /**
     * @var object for the type of database to be save or restored.
     * @access private
     */
    
    var $m_dbObject ;
    
    /**
     * @var resource the file pointer for the input/output file.
     * @access private
     */
    
    var $m_fptr;
    
    /**
     * @var string the name of the output file.
     * @access private
     */
    
    var $m_output;
    
    /**
     * @var boolean TRUE if only the structure of the database is to be saved.
     * @access private
     */
    
    var $m_structureOnly;
    
    function __construct()
    {
    	$this->EE =& get_instance();
        $this->m_structureOnly = FALSE;
        $this->settings = $this->EE->m62_backup_settings->get_settings();
    }
    
    public function set_settings($settings)
    {
    	$this->settings = $settings;
    }

    /**
     * @desc Restore a backup file.
     * @returns void
     * @access public
     */
    
    function restore($store_path, $db_info)
    {
    	
    	if ($this->EE->extensions->active_hook('m62_backup_db_restore_start') === TRUE)
		{
			$this->EE->extensions->call('m62_backup_db_restore_start', $db_info);
			if ($this->EE->extensions->end_script === TRUE) return;
		}
		    	
        $this->m_dbObject =& FactoryDB::factory($db_info['user'], $db_info['pass'], $db_info['db_name'], $db_info['host'], dmDB_MySQL) ;
    	$this->m_output = $store_path;
        $this->m_fptr = fopen($this->m_output, "r") ;
        
        if ($this->m_fptr === FALSE)
        {
            die(sprintf("Can't open %s", $this->m_output)) ;
        }
        
        while (!feof($this->m_fptr))
        {
            $theQuery = fgets($this->m_fptr) ;
            $theQuery = substr($theQuery, 0, strlen($theQuery) - 1) ;
            
            if ($theQuery != '')
            {
                $this->m_dbObject->query(utf8_decode($theQuery)) ;
            }
        }
        
        if ($this->EE->extensions->active_hook('m62_backup_db_restore_end') === TRUE)
		{
			$this->EE->extensions->call('m62_backup_db_restore_end', $db_info);
			if ($this->EE->extensions->end_script === TRUE) return;
		}        
        
        fclose($this->m_fptr);
        return true;
    }

    /**
     * @desc write an SQL statement to the backup file.
     * @param string The string to be written.
     * @access private
     */
    
    function _Out($s)  
    {  
        if ($this->m_fptr === false)
        {
            echo("$s");
        }
        else
        {
            fputs($this->m_fptr, utf8_encode($s));
        }
    }

    /**
     * @desc public interface for backup.
     * @returns void
     * @access public
     */
    
    function backup($store_path, $db_info)
    {
        $this->m_dbObject =& FactoryDB::factory($db_info['user'], $db_info['pass'], $db_info['db_name'], $db_info['host'], dmDB_MySQL) ;
    	$this->m_output = $store_path;
    	$this->m_fptr=fopen($this->m_output,"w");
    	//enumerate tables 

        $total_items = count($this->m_dbObject->showTables());

        $total_items++; //for compression 
        
        if($this->settings['s3_access_key'] != '' && $this->settings['s3_secret_key'] != '')
		{
			$total_items++;
		} 

        if($this->settings['cf_api'] != '' && $this->settings['cf_username'] != '')
		{
			$total_items++;
		} 		

		if($this->settings['ftp_hostname'] != '')
		{
			$total_items++;
		}

        if ($this->EE->extensions->active_hook('m62_backup_db_backup_start') === TRUE)
		{
			$this->EE->extensions->call('m62_backup_db_backup_start', $db_info);
			if ($this->EE->extensions->end_script === TRUE) return;
		}		
		
        $this->m_dbObject->queryConstant('SHOW TABLES');
        $this->EE->m62_backup_lib->write_progress_log(lang('backup_progress_bar_start'), $total_items, 0);
        
        $count = 1;
        while ($theTable =& $this->m_dbObject->fetchRow())
        {
   	
            $theTableName = $theTable[0];
            $this->EE->m62_backup_lib->write_progress_log(lang('backup_progress_bar_table_start').$theTableName, $total_items, $count);
    

            
            $theDB = clone($this->m_dbObject) ;
            $theCreateTable = $theDB->showCreateTable($theTableName) ;
            $theDB->clear() ;
            
            $theCreateTable = preg_replace('/\s*\n\s*/', ' ', $theCreateTable) ;
            $theCreateTable = preg_replace('/\(\s*/', '(', $theCreateTable) ;
            $theCreateTable = preg_replace('/\s*\)/', ')', $theCreateTable) ;
                  
            $this->_Out(sprintf("DROP TABLE IF EXISTS `%s`; \n", $theTableName)) ;
            //$this->_Out("/*!40101 SET @saved_cs_client = @@character_set_client */;\n");
    		//$this->_Out("/*!40101 SET character_set_client = utf8 */;\n");              
            
            $this->_Out($theCreateTable . ";\n");  
            //$this->_Out("/*!40101 SET character_set_client = @saved_cs_client */;\n\n");
    
            if ($this->m_structureOnly != true)  
            {  
                $theDB->queryConstant(sprintf('SELECT * FROM %s', $theTableName)) ;
                
                //parse out the table's data and generate the SQL INSERT statements in order to replicate the data itself... 
                
                $theFieldNames = '' ;
                
                while ($theDataRow =& $theDB->fetchAssoc())
                {
                    if ($theFieldNames == '')
                    {
                        $theFieldNames = '`' . implode('`, `', array_keys($theDataRow)) . '`' ;
                    }

                    $theData = array() ;
                    
                    foreach ($theDataRow as $theValue)
                    {
                        $theData[] = $theDB->escape_string($theValue) ;
                    }
                    
                    $theData = '"' . implode('", "', $theData) . '"' ;
                    
                    $theInsert = sprintf("INSERT INTO `%s` (%s) VALUES (%s) ;\n",
                                         $theTableName, $theFieldNames,
                                         $theData) ;
                                         
                    $this->_Out($theInsert);  
    
                   }  
    
                $this->_Out("\n");  
            }  
    
            $theDB->clear() ;    
            $this->EE->m62_backup_lib->write_progress_log(lang('backup_progress_bar_table_stop').$theTableName, $total_items, $count);
            $count++;
            
        }  
        
        $this->m_dbObject->clear() ;
        
        if ($this->m_fptr!=false)
        {
            fclose($this->m_fptr);
        }

        if ($this->EE->extensions->active_hook('m62_backup_db_backup_end') === TRUE)
		{
			$this->EE->extensions->call('m62_backup_db_backup_end', $db_info);
			if ($this->EE->extensions->end_script === TRUE) return;
		} 

        if ($this->EE->extensions->active_hook('m62_backup_db_backup_zip_start') === TRUE)
		{
			$this->EE->extensions->call('m62_backup_db_backup_zip_start', $this->m_output);
			if ($this->EE->extensions->end_script === TRUE) return;
		}		
        
        $zip = new PclZip62($this->m_output.'.zip');
		if ($zip->create($this->m_output, PCLZIP_OPT_REMOVE_ALL_PATH) == 0) 
		{
			return FALSE;
		}      
		unlink($this->m_output);

        if ($this->EE->extensions->active_hook('m62_backup_db_backup_zip_end') === TRUE)
		{
			$this->EE->extensions->call('m62_backup_db_backup_zip_end', $this->m_output.'.zip');
			if ($this->EE->extensions->end_script === TRUE) return;
		}		
		
		$this->EE->m62_backup_lib->write_progress_log(lang('backup_progress_bar_database_stop'), $total_items, $count);
		
    	if($this->settings['s3_access_key'] != '' && $this->settings['s3_secret_key'] != '')
		{
			$total_items++;
			$this->EE->m62_backup_lib->write_progress_log(lang('backup_progress_bar_start_s3'), $total_items, $count);
			$this->EE->load->library('m62_backup_s3');
			$this->EE->m62_backup_s3->move_backup($this->m_output.'.zip', 'database');		
			$this->EE->m62_backup_lib->write_progress_log(lang('backup_progress_bar_stop_s3'), $total_items, $count);
			$count++;
		}

        if($this->settings['cf_api'] != '' && $this->settings['cf_username'] != '')
		{
			$total_items++;
			$this->EE->m62_backup_lib->write_progress_log(lang('backup_progress_bar_start_cf'), $total_items, $count);
			$this->EE->load->library('m62_backup_cf');
			$this->EE->m62_backup_cf->move_backup($this->m_output.'.zip', 'database');		
			$this->EE->m62_backup_lib->write_progress_log(lang('backup_progress_bar_stop_cf'), $total_items, $count);
			$count++;
		}
				
		if($this->settings['ftp_hostname'] != '')
		{
			$this->EE->m62_backup_lib->write_progress_log(lang('backup_progress_bar_start_ftp'), $total_items, $count);	
			$this->EE->load->library('m62_backup_ftp');
			$this->EE->m62_backup_ftp->move_backup($this->m_output.'.zip', 'database');		
			$this->EE->m62_backup_lib->write_progress_log(lang('backup_progress_bar_stop_ftp'), $total_items, $count);
		}
		
		$this->EE->m62_backup_lib->write_progress_log(lang('backup_progress_bar_stop'), $total_items, $total_items);	
        return $this->m_output.'.zip';
    }
}
?>