<?php


class phpx_page {
	
	var $scope = 'admin';
	
	public function startPage($title='', $status='', $insideTitle='', $extra=''){
        $status = ($status != '') ? '<div class="status">' . $status . '</div>' : '';
        $insideTitle = ($insideTitle != '') ? '<h3>' . $insideTitle . '</h3>' : '';
        
        
        $text = '<div class="wrap" id="phpxContainer">';
        $text .= '<h2>' . $title . '</h2>';
        $text .= '<div id="poststuff" class="metabox-holder">';
        $text .= '<div id="post-body" class="has-sidebar">';
        $text .= '<div id="post-body-content" class="has-sidebar-content">';
        $text .= $extra;
        $text .= '<div class="postbox">';
        $text .= $insideTitle;
        $text .= $status;
        $text .= '<div class="inside">';     		
        return $text;
	}
	
	public function endPage(){
		$text = '</div></div></div></div></div></div>';
		return $text;
	}
	
	
	
}
?>
