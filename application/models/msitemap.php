<?php
class MSitemap extends CI_Model{
	protected $_table="articles";
	public function __construct(){
		parent::__construct();
	}
	
	public function listItem($arrParam = null, $options = null){
		$site_domain = $arrParam['site_domain'];
		if( strpos($site_domain, 'http') === false){
			$site_domain = 'http://' . $site_domain;
		}
		$arrSitemap = '';
		if($options['task'] == 'admin-list'){
			// Trang chu ========================
			$arrSitemap[] = array(
					'loc' => $site_domain,
					'lastmod' => '',
					'changefreq' => '',
					'priority' => '',
			);
			
			$this->db->select('id, name, alias');
			$result = $this->db->get('article_category')->result_array();
			
			$this->db->flush_cache();
			if(!empty($result)) {
				
				foreach ($result AS $key => $val) {
					$linkCategory = $val['alias'] . '-a' . $val['id'] . '.html';
					$arrSitemap[] = array(
							'loc' => $site_domain . '/' . $linkCategory,
							'lastmod' => '',
							'changefreq' => '',
							'priority' => '',
					);
				}
			}
			
			$this->db->select('a.id, a.name, a.alias, id_cat, ac.alias as category_alias');
			$this->db->join('article_category as ac','ac.id = a.id_cat','left');
			$result = $this->db->get('articles as a')->result_array();
			
			$this->db->flush_cache();
			if(!empty($result)) {
				foreach ($result AS $key => $val) {
					$linkDetail = $val ['alias'] . '-a' . $val['id_cat'] . 'i' . $val['id'] . '.html';
					$arrSitemap[] = array(
							'loc' => $site_domain . '/' . $linkDetail,
							'lastmod' => '',
							'changefreq' => '',
							'priority' => '',
					);
				}

			}
		}
		
		return $arrSitemap;
	}
	
}	