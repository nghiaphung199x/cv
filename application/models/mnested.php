<?php 
class MNested extends CI_Model{
	protected $_table="article_category";
    public $_id;
    public $_data;
    public $_parent_id;
	
	public function __construct(){
		parent::__construct();
	}
	
	public function removeNode($id,$options = 'branch'){
		$this->_id = $id;
	
		if($options == 'branch' || $options == null ) $this->removeBranch();
	
		if($options == 'one') $this->removeOne();
	}
	
	protected function removeBranch(){
		/*================================================
		 *  1. Lay thong cua node bi xoa
		/*================================================*/
		$infoNodeRemove  = $this->getNodeInfo($this->_id);
		
		if(!empty($infoNodeRemove)) {
			/*================================================
			 *  2. Tinh chieu dai cua nhanh chung muon xoa
			/*================================================*/
			$widthNodeRemove = $this->widthNode($infoNodeRemove['lft'], $infoNodeRemove['rgt']);
			
			/*================================================
			 *  3. Xoa nhanh
			/*================================================*/
			$sqlDelete = 'DELETE FROM ' .$this->_table . '
						  WHERE lft BETWEEN ' . $infoNodeRemove['lft'] . ' AND ' . $infoNodeRemove['rgt'];

			
			$this->db->query($sqlDelete);
			
			/*================================================
			 *  4. Cap nhat lai cai gia tri left - right của cay
			/*================================================*/
			$sqlUpdateLeft = ' UPDATE ' . $this->_table . '
							   SET lft = (lft - ' . $widthNodeRemove . ')
							   WHERE lft > ' . $infoNodeRemove['rgt'];
			
			$this->db->query($sqlUpdateLeft);
			
			
			$sqlUpdateRight = ' UPDATE ' . $this->_table . '
								SET rgt = (rgt - ' . $widthNodeRemove . ')
								WHERE rgt > ' . $infoNodeRemove['rgt'];
			
			$this->db->query($sqlUpdateRight);
		}
	
	}
	
	protected function removeOne(){
		$nodeInfo = $this->getNodeInfo($this->_id);
		$this->db->select('id')
				 ->where('parent', $this->_id)
				 ->order_by('lft', 'DESC');
		
		$childIdsTmp =  $this->db->get($this->_table)->result_array();
		$this->db->flush_cache();
		if(!empty($childIdsTmp)) {
			foreach($childIdsTmp as $child)
				$childIds[] = $child['id'];
			
			foreach ($childIds as $val){
				$id = $val;
				$parent = $nodeInfo['parent'];
				$options = array('position' => 'after', 'brother_id' => $nodeInfo['id']);
				$this->moveNode($id, $parent,$options);
			}
			
			$this->removeNode($nodeInfo['id']);
		}

	}
	public function updateNode($data, $id = null, $newParentID = 0){
		if($id != 0 && $id > 0 && count($data)!=0  ){
			$infoNode = $this->getNodeInfo($id);
			$this->db->where('id', $id);
			$this->db->update($this->_table,$data);
			$this->db->flush_cache();
	
			if($newParentID > 0 && $newParentID != null){
				if($infoNode['parent'] != $newParentID){
					$this->moveNode($id, $newParentID);
				}
			}
		}
		 
	
	}
	public function moveNode($id, $parent, $options = null){
		$this->_id = $id;
		$this->_parent_id = $parent;
	
		if($options['position'] == 'right' || $options == null ) $this->moveRight();
	
		if($options['position'] == 'left') $this->moveLeft();
	
		if($options['position'] == 'before') $this->moveBefore($options['brother_id']);
	
		if($options['position'] == 'after') $this->moveAfter($options['brother_id']);
	
	}
	
	public function moveUp($id){
		$infoMoveNode = $this->getNodeInfo($id);
		$infoParentNode = $this->getNodeInfo($infoMoveNode['parent']);
		$this->db->select('*')
				 ->where('parent', $infoMoveNode['parent'])
				 ->where('lft < ' . $infoMoveNode['lft'])
				 ->order_by('lft', 'DESC')
				 ->limit(1);
		
		$infoBrotherNode =  $this->db->get($this->_table)->row_array();
		
		$this->db->flush_cache();
	
		$this->db->select('*')
				 ->where('parent', $infoMoveNode['parent'])
				 ->where('lft < ' . $infoBrotherNode['lft'])
				 ->order_by('lft', 'DESC')
				 ->limit(1);
	
		$infoLeftNode =  $this->db->get($this->_table)->row_array();
		$this->db->flush_cache();

		if(!empty($infoBrotherNode) && !empty($infoLeftNode)){
			$options = array('position'=>'before','brother_id'=>$infoBrotherNode['id']);
			$this->moveNode($id, $infoMoveNode['parent'],$options);
		}else{
			$options = array('position'=>'left');
			$this->moveNode($id, $infoMoveNode['parent'],$options);
		}
		 
	}
	
	public function moveDown($id){
		$infoMoveNode = $this->getNodeInfo($id);

		$infoParentNode = $this->getNodeInfo($infoMoveNode['parent']);

		$this->db->select('*')
				  ->where('parent = ' . $infoMoveNode['parent'])
				  ->where('lft > ' . $infoMoveNode['lft'])
				  ->order_by('lft', 'ASC')
				  ->limit(1);
		
		$infoBrotherNode =  $this->db->get($this->_table)->row_array();
		
		$this->db->flush_cache();
		
		$this->db->select('*')
				  ->where('parent = ' . $infoMoveNode['parent'])
				  ->where('lft > ' . $infoBrotherNode['lft'])
				  ->order_by('lft', 'ASC')
				  ->limit(1);
		
		$infoRightNode =  $this->db->get($this->_table)->row_array();
		$this->db->flush_cache();
	 
		if(!empty($infoBrotherNode) && !empty($infoRightNode)){
			$options = array('position'=>'after','brother_id'=>$infoBrotherNode['id']);
			$this->moveNode($id, $infoMoveNode['parent'],$options);
		}else{
			$this->moveNode($id, $infoMoveNode['parent']);
		}
	
	}
	
	protected function moveRight(){
		$infoMoveNode = $this->getNodeInfo($this->_id);
	
		$lftMoveNode = $infoMoveNode['lft']; // 3
		$rgtMoveNode = $infoMoveNode['rgt']; // 6
		 
		/*================================================
		 *  1. Tinh do dai cua nhanh chung ta cat
		/*================================================*/
		$widthMoveNode = $this->widthNode($lftMoveNode, $rgtMoveNode);
	
		/*================================================
		 *  2. Tach nhanh khoi cay
		/*================================================*/
	
		$sqlReset = 'UPDATE ' . $this->_table . '
					 SET lft = (lft - ' . $lftMoveNode . '),
					 rgt = (rgt - ' . $rgtMoveNode . ')
					 WHERE lft BETWEEN ' . $lftMoveNode . ' AND ' . $rgtMoveNode;
	
		$this->db->query($sqlReset);
	
	
		/*================================================
		 *  3. Cap nhat gia tri cac node nam ben phai cua node tach
		/*================================================*/
	
		$sqlUpdateRight = 'UPDATE ' . $this->_table . '
						   SET rgt = (rgt - ' . $widthMoveNode . ')
						   WHERE rgt > ' . $rgtMoveNode;
		 
		$this->db->query($sqlUpdateRight);
	
		$sqlUpdateLeft = 'UPDATE ' . $this->_table . '
						  SET lft = (lft - ' . $widthMoveNode . ')
						  WHERE lft > ' . $rgtMoveNode;
	
		$this->db->query($sqlUpdateLeft);
	
		/*================================================
		 *  4. Lay ra thong thong tin cua node cha ($infoParentNode)
		/*================================================*/
		$infoParentNode = $this->getNodeInfo($this->_parent_id);
		$rgtParentNode = $infoParentNode['rgt'];
	
	
		/*================================================
		 * 5. Cap nhat cac gia tri truoc khi gan nhanh vao
		/*================================================*/
	
		$sqlUpdateLeft = ' UPDATE ' . $this->_table . '
						   SET lft = (lft + ' . $widthMoveNode . ')
						   WHERE lft >= ' . $rgtParentNode . '
						   AND rgt > 0';
		$this->db->query($sqlUpdateLeft);
	
	
		$sqlUpdateRight = ' UPDATE ' . $this->_table . '
							SET rgt = (rgt + ' . $widthMoveNode . ')
							WHERE rgt >= ' . $rgtParentNode;
	
		$this->db->query($sqlUpdateRight);
	
	
		/*================================================
		 * 6. Cap nhat level cho nhanh sap dc gan vao cay
		/*================================================*/
		$levelMoveNode = $infoMoveNode['level'];
		$levelParentNode = $infoParentNode['level'];
		$sqlUpdateLevel = 'UPDATE ' . $this->_table . '
						   SET level = (level - ' . $levelMoveNode . '+' . $levelParentNode . '+ 1)
						   WHERE rgt <=0';
	
		$this->db->query($sqlUpdateLevel);
	
	
		/*================================================
		 * 7. Cap nhat nhanh truoc khi gan vao node moi
		/*================================================*/
		$sqlUpdateLeft = ' UPDATE ' . $this->_table . '
						   SET lft = (lft + ' . $infoParentNode['rgt'] . ')
						   WHERE rgt <= 0 ';
		$this->db->query($sqlUpdateLeft);
	
	
		$sqlUpdateRight = ' UPDATE ' . $this->_table . '
							SET rgt = (rgt + ' . $infoParentNode['rgt'] . '+' . $widthMoveNode . '- 1)
							WHERE rgt <= 0 ';
	
		$this->db->query($sqlUpdateRight);
	
	
		/*================================================
		 * 8. Gan vao node cha
		/*================================================*/
	
		$sqlUpdateNode = ' UPDATE ' . $this->_table . '
						   SET parent = ' .  $infoParentNode['id'] . '
						   WHERE id = ' . $infoMoveNode['id'];
	
		$this->db->query($sqlUpdateNode);
	
	}
	
	protected function moveLeft(){
		$infoMoveNode = $this->getNodeInfo($this->_id);
		$lftMoveNode = $infoMoveNode['lft']; // 3
		$rgtMoveNode = $infoMoveNode['rgt']; // 6
		 
		/*================================================
		 *  1. Tinh do dai cua nhanh chung ta cat
		/*================================================*/
		$widthMoveNode = $this->widthNode($lftMoveNode, $rgtMoveNode);
		 
		/*================================================
		 *  2. Tach nhanh khoi cay
		/*================================================*/
	
		$sqlReset = 'UPDATE ' . $this->_table . '
					SET lft = (lft - ' . $lftMoveNode . '),
					rgt = (rgt - ' . $rgtMoveNode . ')
					WHERE lft BETWEEN ' . $lftMoveNode . ' AND ' . $rgtMoveNode;
	
		$this->db->query($sqlReset);
	
	
		/*================================================
		 *  3. Cap nhat gia tri cac node nam ben phai cua node tach
		/*================================================*/
	
		$sqlUpdateLeft = ' UPDATE ' . $this->_table . '
						   SET lft = (lft - ' . $widthMoveNode . ')
						   WHERE lft > ' . $rgtMoveNode;
	
		$this->db->query($sqlUpdateLeft);
	
	
		$sqlUpdateRight = ' UPDATE ' . $this->_table . '
							SET rgt = (rgt - ' . $widthMoveNode . ')
							WHERE rgt > ' . $rgtMoveNode;
	
		$query = $this->db->query($sqlUpdateRight);
	
	
		/*================================================
		 *  4. Lay ra thong thong tin cua node cha ($infoParentNode)
		/*================================================*/
	
		$infoParentNode = $this->getNodeInfo($this->_parent_id);
		$lftParentNode = $infoParentNode['lft'];
	
	
		/*================================================
		 * 5. Cap nhat cac gia tri truoc khi gan nhanh vao
		/*================================================*/
	
		$sqlUpdateLeft = ' UPDATE ' . $this->_table . '
						   SET lft = (lft + ' . $widthMoveNode . ')
						   WHERE lft > ' . $lftParentNode .'
						   AND rgt >0';
	
		$this->db->query($sqlUpdateLeft);
	
		$sqlUpdateRight = ' UPDATE ' . $this->_table . '
							SET rgt = (rgt + ' . $widthMoveNode . ')
							WHERE rgt > ' . $lftParentNode;
	
		$this->db->query($sqlUpdateRight);
		 
		 
		/*================================================
		 * 6. Cap nhat level cho nhanh sap dc gan vao cay
		/*================================================*/
	
		$levelMoveNode = $infoMoveNode['level'];
		$levelParentNode = $infoParentNode['level'];
		$sqlUpdateLevel = 'UPDATE ' . $this->_table . '
						   SET level = (level - ' . $levelMoveNode . '+' . $levelParentNode . '+ 1)
						   WHERE rgt <=0';
	
		$this->db->query($sqlUpdateLevel);
	
		/*================================================
		 * 7. Cap nhat nhanh truoc khi gan vao node moi
		/*================================================*/
	
		$sqlUpdateLeft = 'UPDATE ' . $this->_table . '
						  SET lft = (lft + ' . $infoParentNode['lft'] . '+' . ' 1)
						  WHERE rgt <= 0 ';
	
		$this->db->query($sqlUpdateLeft);
	
		$sqlUpdateRight = ' UPDATE ' . $this->_table . '
							SET rgt = (rgt + ' . $infoParentNode['lft'] . '+' . $widthMoveNode . ')
							WHERE rgt <= 0 ';
		 
		$this->db->query($sqlUpdateRight);
	
	
		/*================================================
		 * 8. Gan vao node cha
		/*================================================*/
	
		$sqlUpdateNode = ' UPDATE ' . $this->_table . '
						   SET parent = ' .  $infoParentNode['id'] . '
						   WHERE id = ' . $infoMoveNode['id'];
	
		$this->db->query($sqlUpdateNode);
	
	}
	
	protected function moveBefore($brother_id){
		$infoMoveNode = $this->getNodeInfo($this->_id);
	
		$lftMoveNode = $infoMoveNode['lft']; // 3
		$rgtMoveNode = $infoMoveNode['rgt']; // 6
	
	
		/*================================================
		 *  1. Tach nhanh khoi cay
		/*================================================*/
	
		$sqlSelect = 'UPDATE ' . $this->_table . '
					  SET lft = (lft - ' . $lftMoveNode . '),
					  rgt = (rgt - ' . $rgtMoveNode . ')
					  WHERE lft BETWEEN ' . $lftMoveNode . ' AND ' . $rgtMoveNode;
		$this->db->query($sqlSelect);
	
	
		/*================================================
		 *  2. Tinh do dai cua nhanh chung ta cat
		/*================================================*/
		$widthMoveNode = $this->widthNode($lftMoveNode, $rgtMoveNode);
	
	
		/*================================================
		 *  3. Cap nhat gia tri cac node nam ben phai cua node tach
		/*================================================*/
	
		$sqlUpdateLeft = ' UPDATE ' . $this->_table . '
						   SET lft = (lft - ' . $widthMoveNode . ')
						   WHERE lft > ' . $rgtMoveNode;
	
		$this->db->query($sqlUpdateLeft);
	
		$sqlUpdateRight = ' UPDATE ' . $this->_table . '
							SET rgt = (rgt - ' . $widthMoveNode . ')
							WHERE rgt > ' . $rgtMoveNode;
		$this->db->query($sqlUpdateRight);
	
	
		/*================================================
		 *  4. Lay ra thong thong tin cua node cha ($infoParentNode)
		/*================================================*/
	
		$infoParentNode = $this->getNodeInfo($this->_parent_id);
	
		/*================================================
		 *  5. Lay gia tri cua node brother ($infoBrotherNode)
		/*================================================*/
	
		$infoBrotherNode = $this->getNodeInfo($brother_id);
		$lftBrotherNode  = $infoBrotherNode['lft'];
	
	
		/*================================================
		 * 6. Cap nhat cac gia tri truoc khi gan nhanh vao
		/*================================================*/
		$sqlUpdateLeft = ' UPDATE ' . $this->_table . '
							SET lft = (lft + ' . $widthMoveNode . ')
							WHERE lft >= ' . $lftBrotherNode;
	
		$this->db->query($sqlUpdateLeft);
	
		$sqlUpdateRight = ' UPDATE ' . $this->_table . '
							SET rgt = (rgt + ' . $widthMoveNode . ')
							WHERE rgt > ' . $lftBrotherNode;
	
		$this->db->query($sqlUpdateRight);
	
		/*================================================
		 * 7. Cap nhat level cho nhanh sap dc gan vao cay
		/*================================================*/
		$levelMoveNode = $infoMoveNode['level'];
		$levelParentNode = $infoParentNode['level'];
		$sqlUpdateLevel = 'UPDATE ' . $this->_table . '
						   SET level = (level - ' . $levelMoveNode . '+' . $levelParentNode . '+ 1)
						   WHERE rgt <=0';
	
		$this->db->query($sqlUpdateLevel);
	
	
		/*================================================
		 * 8. Cap nhat nhanh truoc khi gan vao node moi
		/*================================================*/
	
		$sqlUpdateLeft = ' UPDATE ' . $this->_table . '
						   SET lft = (lft + ' . $lftBrotherNode . ')
						   WHERE rgt <= 0 ';
	
		$this->db->query($sqlUpdateLeft);
	
		$sqlUpdateRight = ' UPDATE ' . $this->_table . '
							SET rgt = (rgt + ' . $lftBrotherNode . '+' . $widthMoveNode . '- 1)
							WHERE rgt <= 0 ';
	
		$this->db->query($sqlUpdateRight);
	
	
	
		/*================================================
		 * 9. Gan vao node cha
		/*================================================*/
		$sqlUpdateNode = ' UPDATE ' . $this->_table . '
						   SET parent = ' .  $infoParentNode['id'] . '
						   WHERE id = ' . $infoMoveNode['id'];
	
	
		$this->db->query($sqlUpdateNode);
	}
	
	protected function moveAfter($brother_id){
		$infoMoveNode = $this->getNodeInfo($this->_id);
	
		$lftMoveNode = $infoMoveNode['lft']; // 3
		$rgtMoveNode = $infoMoveNode['rgt']; // 6
	
		/*================================================
		 *  1. Tach nhanh khoi cay
		/*================================================*/
	
		$sqlSelect = 'UPDATE ' . $this->_table . '
					  SET lft = (lft - ' . $lftMoveNode . '),
					  rgt = (rgt - ' . $rgtMoveNode . ')
					  WHERE lft BETWEEN ' . $lftMoveNode . ' AND ' . $rgtMoveNode;
	
		$this->db->query($sqlSelect);
	
	
		/*================================================
		 *  2. Tinh do dai cua nhanh chung ta cat
		/*================================================*/
		$widthMoveNode = $this->widthNode($lftMoveNode, $rgtMoveNode);
	
		/*================================================
		 *  3. Cap nhat gia tri cac node nam ben phai cua node tach
		/*================================================*/
	
		$sqlUpdateLeft = ' UPDATE ' . $this->_table . '
						   SET lft = (lft - ' . $widthMoveNode . ')
						   WHERE lft > ' . $rgtMoveNode;
	
		$this->db->query($sqlUpdateLeft);
	
	
		$sqlUpdateRight = ' UPDATE ' . $this->_table . '
							SET rgt = (rgt - ' . $widthMoveNode . ')
							WHERE rgt > ' . $rgtMoveNode;
	
		$this->db->query($sqlUpdateRight);
	
		/*================================================
		 *  4. Lay ra thong thong tin cua node cha ($infoParentNode)
		/*================================================*/
	
		$infoParentNode = $this->getNodeInfo($this->_parent_id);
	
		/*================================================
		 *  5. Lay gia tri cua node brother ($infoBrotherNode)
		/*================================================*/
	
		$infoBrotherNode = $this->getNodeInfo($brother_id);
		$rgtBrotherNode  = $infoBrotherNode['rgt'];
	
		/*================================================
		 * 6. Cap nhat cac gia tri truoc khi gan nhanh vao
		/*================================================*/
		$sqlUpdateLeft = ' UPDATE ' . $this->_table . '
						   SET lft = (lft + ' . $widthMoveNode . ')
						   WHERE lft > ' . $rgtBrotherNode;
	
		$this->db->query($sqlUpdateLeft);
	
		$sqlUpdateRight = ' UPDATE ' . $this->_table . '
							SET rgt = (rgt + ' . $widthMoveNode . ')
							WHERE rgt > ' . $rgtBrotherNode;
	
		$this->db->query($sqlUpdateRight);
	
		/*================================================
		 * 7. Cap nhat level cho nhanh sap dc gan vao cay
		/*================================================*/
		$levelMoveNode = $infoMoveNode['level'];
		$levelParentNode = $infoParentNode['level'];
		$sqlUpdateLevel = 'UPDATE ' . $this->_table . '
						   SET level = (level - ' . $levelMoveNode . '+' . $levelParentNode . '+ 1)
						   WHERE rgt <=0';
	
		$this->db->query($sqlUpdateLevel);
	
		/*================================================
		 * 8. Cap nhat nhanh truoc khi gan vao node moi
		/*================================================*/
		$sqlUpdateLeft = ' UPDATE ' . $this->_table . '
						   SET lft = (lft + ' . $rgtBrotherNode . '+ 1)
						   WHERE rgt <= 0 ';
	
		$this->db->query($sqlUpdateLeft);
	
		$sqlUpdateRight = ' UPDATE ' . $this->_table . '
							SET rgt = (rgt + ' . $rgtBrotherNode . '+' . $widthMoveNode . ')
							WHERE rgt <= 0 ';
	
		$this->db->query($sqlUpdateRight);
	
		/*================================================
		 * 9. Gan vao node cha
		/*================================================*/
		$sqlUpdateNode = ' UPDATE ' . $this->_table . '
						   SET parent = ' .  $infoParentNode['id'] . '
						   WHERE id = ' . $infoMoveNode['id'];
	
	
		$this->db->query($sqlUpdateNode);
	
	}
	
	public function widthNode($lftMoveNode,$rgtMoveNode){
		$widthMoveNode = $rgtMoveNode - $lftMoveNode + 1;
		return $widthMoveNode;
	}
	
	public function insertNode($data,$parent = 1,$options = null){
		$this->_data = $data;
		$this->_parent_id = $parent;
	
		if($options['position'] == 'right' || $options == null ) $newId = $this->insertRight();
	
		if($options['position'] == 'left') $newId = $this->insertLeft();
	
		if($options['position'] == 'before') $newId = $this->insertBefore($options['brother_id']);
	
		if($options['position'] == 'after') $newId = $this->insertAfter($options['brother_id']);
	
		return $newId;
	}
	
	protected function insertAfter($brother_id){
		$parentInfo  = $this->getNodeInfo($this->_parent_id);
		 
		$brothderInfo = $this->getNodeInfo($brother_id);
		 
		$sqlUpdateLeft = 'UPDATE ' .$this->_table
					  . ' SET lft = (lft + 2) '
					  . ' WHERE lft > ' . $brothderInfo['rgt'];
			
		$this->db->query($sqlUpdateLeft);
	
		$sqlUpdateRight = 'UPDATE ' .$this->_table
					   . ' SET rgt = (rgt + 2) '
					   . ' WHERE rgt > ' . $brothderInfo['rgt'];
			
		$this->db->query($sqlUpdateRight);
		 
		$data = $this->_data;
		$data['parent'] 	= $parentInfo['id']; //$this->_parent_id
		$data['lft'] 		= $brothderInfo['rgt'] + 1;
		$data['rgt'] 		= $brothderInfo['rgt'] + 2;
		$data['level'] 		= $parentInfo['level'] + 1;
		
		$this->db->insert($this->_table,$data);
		$newId = $this->db->insert_id();

		return $newId;
	}
	

	protected function insertBefore($brother_id){
		$parentInfo  = $this->getNodeInfo($this->_parent_id);
	
		$brothderInfo = $this->getNodeInfo($brother_id);
	
		$sqlUpdateLeft = 'UPDATE ' .$this->_table
					  . ' SET lft = (lft + 2) '
					  . ' WHERE lft >= ' . $brothderInfo['lft'];
			
		$this->db->query($sqlUpdateLeft);
		 
		$sqlUpdateRight = 'UPDATE ' .$this->_table
					  . ' SET rgt = (rgt + 2) '
					  . ' WHERE rgt >= ' . ($brothderInfo['lft'] + 1);
			  
		$this->db->query($sqlUpdateRight);
		 
		$data = $this->_data;
		$data['parent'] 	= $parentInfo['id']; //$this->_parent_id
		$data['lft'] 		= $brothderInfo['lft'];
		$data['rgt'] 		= $brothderInfo['lft']+1;
		$data['level'] 		= $parentInfo['level'] + 1;
		
		$this->db->insert($this->_table,$data);
		$newId = $this->db->insert_id();

		return $newId;
	
	}
	
	protected function insertLeft(){
		$parentInfo  = $this->getNodeInfo($this->_parent_id);
	
		$parentLeft = $parentInfo['lft'];
	
		$sqlUpdateLeft = 'UPDATE ' .$this->_table
					  . ' SET lft = (lft + 2) '
					  . ' WHERE lft >= ' . ($parentLeft + 1);
		$this->db->query($sqlUpdateLeft);
	
		$sqlUpdateRight = 'UPDATE ' .$this->_table
					  . ' SET rgt = (rgt + 2) '
					  . ' WHERE rgt > ' . ($parentLeft + 1);
		$this->db->query($sqlUpdateRight);
		 
		$data = $this->_data;
		$data['parent'] 	= $parentInfo['id']; //$this->_parent_id
		$data['lft'] 		= $parentLeft + 1;
		$data['rgt'] 		= $parentLeft + 2;
		$data['level'] 		= $parentInfo['level'] + 1;
		
		$this->db->insert($this->_table,$data);
		$newId = $this->db->insert_id();

		return $newId;
	}
	
	protected function insertRight(){
		$parentInfo  = $this->getNodeInfo($this->_parent_id);
		$parentRight = $parentInfo['rgt'];
		 
		$sqlUpdateLeft = 'UPDATE ' .$this->_table
					  . ' SET lft = (lft + 2) '
					  . ' WHERE lft > ' . $parentRight;
		 
		$this->db->query($sqlUpdateLeft);
	
		 
		$sqlUpdateRight = 'UPDATE ' .$this->_table
					   . ' SET rgt = (rgt + 2) '
					   . ' WHERE rgt >= ' . $parentRight;
	   
		$this->db->query($sqlUpdateRight);
	
		 
		$data = $this->_data;
		$data['parent'] 	= $parentInfo['id']; //$this->_parent_id
		$data['lft'] 		= $parentRight;
		$data['rgt'] 		= $parentRight + 1;
		$data['level'] 		= $parentInfo['level'] + 1;
		
		$this->db->insert($this->_table,$data);
		$newId = $this->db->insert_id();
		
		$this->db->flush_cache();

		return $newId;
	}
	
	public function getNodeInfo($id){
		$this->db->select('*')
			 ->where('id', $id);
		
		$result = $this->db->get($this->_table)->row_array();
		$this->db->flush_cache();
		 
		return $result;
	}
}