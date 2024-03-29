<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Johannes Krausmueller (johannes@schosemail.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Plugin 'Poll' for the 'jk_poll' extension.
 *
 * @author	Johannes Krausmueller <johannes@schosemail.de>
*/


require_once(PATH_tslib."class.tslib_pibase.php");

class tx_jkpoll_pi1 extends tslib_pibase {
	var $prefixId = "tx_jkpoll_pi1";		// Same as class name
	var $scriptRelPath = "pi1/class.tx_jkpoll_pi1.php";	// Path to this script relative to the extension dir.
	var $extKey = "jk_poll";	// The extension key.
	
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_initPIflexForm();
		
		// this will convert any string which is supplied as $_SERVER['REMOTE_ADDR'] into a valid ip address
		$this->REMOTE_ADDR = long2ip(ip2long($_SERVER['REMOTE_ADDR']));
		
		//Get translated text labels from locallang
		$this->LL_no_poll_found = $this->pi_getLL('no_poll_found');
		$this->LL_poll_not_visible = $this->pi_getLL('poll_not_visible');
		$this->LL_votes_total = $this->pi_getLL('votes_total');
		$this->LL_votes_label = $this->pi_getLL('votes_label');
		$this->LL_amount_votes_label = $this->pi_getLL('amount_votes_label');
		$this->LL_submit_button = $this->pi_getLL('submit_button');
		$this->LL_linklist = $this->pi_getLL('linklist');
		$this->LL_has_voted = $this->pi_getLL('has_voted');
		$this->LL_error_no_vote = $this->pi_getLL('error_no_vote');
		$this->LL_wrong_captcha = $this->pi_getLL('wrong_captcha');
		$this->LL_error_no_vote_selected = $this->pi_getLL('error_no_vote_selected');

		//Get ID of poll ($this->PollID) or error msg. if no poll was found
		if (!$this->getPollID()) {
			$content = '<div class="error">'. $this->LL_no_poll_found. '</div>';
			return $this->pi_wrapInBaseClass($content);
		}
		
		//Get template-file
		if ($this->conf['template'])
		    $this->templateCode = $this->cObj->fileResource($this->conf['template']);
		else
		{
		    if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'templatefile','s_template') != "" && !(is_null($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'templatefile','s_template'))))
        		$this->templateCode = $this->cObj->fileResource($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'templatefile','s_template'));
    		    else 
        		$this->templateCode = $this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey).'poll.tmpl');
		}
        
    	//Poll should be displayed	
		if (strchr($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'what_to_display','sDEF'),"POLL") || $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'what_to_display','sDEF')=='' )
		{	
			//The Get/Post variables
			$postVars = t3lib_div::_GP($this->prefixId);
			$getVars = t3lib_div::_GET($this->prefixId); 
			$this->go = $getVars['go'];
			$this->answer = $postVars['answer'];
			$this->captcha = $postVars['captcha'];
			
			switch ($this->go) {
				case 'savevote':
					$content = $this->savevote();
					break;
				default:
					$content = $this->showpoll();
			};
		}
		//List should be displayed	
		if (strchr($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'what_to_display','sDEF'),"LIST") || ($this->go) == 'list' )
			$content = $this->showlist();
		//Result should be displayed	
		if (strchr($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'what_to_display','sDEF'),"RESULT") || ($this->go) == 'result' )
			$content = $this->showresults();
		
		return $this->pi_wrapInBaseClass($content);
	}
	
	/**
     * Shows the poll questions and lets the user votes for one answer or shows results if user already voted
     *
     * @return   string      HTML to display in frontend
     */
	function showpoll() {
		
		//Get poll data
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_jkpoll_poll', 'uid=' .$this->pollID. ' AND sys_language_uid='.$GLOBALS['TSFE']->sys_language_content);
		if($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
		
			//Put answers and votes in array
			$answers = explode("\n", $row['answers']);
			$votes = explode("\n", $row['votes']);
			
			//Put in a 0 if there are no votes yet:
	        $needsupdate = false;
	        foreach ($answers as $i => $a) {	        	
	            if (!is_numeric(trim($votes[$i])) || $votes[$i] == '') {
	                $votes[$i] = '0';
	                $needsupdate = true;
	            }
	        }
	        // write votes back to DB
	        if ($needsupdate) {
	            $dataArr['votes'] = implode("\n",$votes);
	            $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_jkpoll_poll', 'uid='.$this->pollID, $dataArr);
	        }
			
			$template = array();
	    	$template['poll_header'] = $this->cObj->getSubpart($this->templateCode,"###POLL_HEADER###"); 
	    	$template['poll_vote'] = $this->cObj->getSubpart($this->templateCode,"###POLL_VOTE###");  
	    	$template['answer'] = $this->cObj->getSubpart($this->templateCode,"###ANSWER_VOTE###");
	        
	    	// replace poll_header
	    	$markerArrayQuestion = array();
			$markerArrayQuestion["###TITLE###"] = $row['title'];
			$markerArrayQuestion["###QUESTION_IMAGE###"] = $this->getimage($this->pollID);
			$markerArrayQuestion["###QUESTIONTEXT###"] = $this->cObj->stdWrap($row['question'],$this->conf['rtefield_stdWrap.']);
			$content .= $this->cObj->substituteMarkerArray($template["poll_header"],$markerArrayQuestion);
			
			if ((!$this->conf['check_language_specific'] && !$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'check_language_specific','language')) && $this->pollID_parent != 0)
				$check_poll_id = $this->pollID_parent;
			else
				$check_poll_id = $this->pollID;		

			//Check if poll is still voteable
			if ($row['valid_till'] != 0)
				if (time() > $row['valid_till'])
					$this->valid = 0;
				else 
					$this->valid = 1;
			else 
				$this->valid = 1;
	
			//Check for logged IPs
			if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'check_ip','sDEF') || $this->conf['check_ip']) {
				//get timestamp after which vote is possible again
				if ($this->conf['check_ip_time'] != "") 
					$vote_time = time() - (intval($this->conf['check_ip_time']) * 3600);
				elseif ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'time','sDEF') != "") 
					$vote_time = time() - (intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'time','sDEF')) * 3600);
				else 
					$vote_time = time();
					
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_jkpoll_iplog', 'pid='.$check_poll_id.' AND ip='.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->REMOTE_ADDR, 'tx_jkpoll_iplog').' AND tstamp >= '.$vote_time);
				$rows = array();
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					$rows[] = $row;
				}
				if (count($rows))
					$ip_voted = 1;
				else 
					$ip_voted = 0;	
			}
			else 
				$ip_voted = 0;	
			
			
			//Check for fe_users who already voted
			if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'fe_user','sDEF') || $this->conf['check_user']) {
				if ($GLOBALS['TSFE']->fe_user->user['uid'] != '') {
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_jkpoll_userlog', 'pid='.$check_poll_id.' AND fe_user=\''.$GLOBALS['TSFE']->fe_user->user['uid'].'\'');
					$rows = array();
					while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
						$rows[] = $row;
					}
					if (count($rows))
						$user_voted = 1;
					else 
						$user_voted = 0;
				}
				else 
					$user_voted = 1;
			}
			else 
				$user_voted = 0;
	
			//Check for cookie. If not found show poll, if found show results.
			$cookieName = 't3_tx_jkpoll_'.$check_poll_id;
			if (!isset($_COOKIE[$cookieName]) && !$ip_voted && !$user_voted && $this->voteable && $this->valid) {
				
				//Make radio buttons
				foreach ($answers as $i => $a) {
					$markerArrayAnswer = array();
					if ($i == 0 && ($this->conf['first_answer_selected'] || $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'first_answer_selected','s_template')))
						$markerArrayAnswer["###ANSWERTEXT_FORM###"] = '<input class="pollanswer" name="'. $this->prefixId. '[answer]" type="radio" checked="checked" value="'. $i .'" />';
					else
						$markerArrayAnswer["###ANSWERTEXT_FORM###"] = '<input class="pollanswer" name="'. $this->prefixId. '[answer]" type="radio" value="'. $i .'" />';
					$markerArrayAnswer["###ANSWERTEXT_VALUE###"] = $answers[$i];
					$resultcontentAnswer .= $this->cObj->substituteMarkerArrayCached($template['answer'],$markerArrayAnswer);
				}
				
				//build url for form
				$getParams = array($this->prefixId.'[go]' => 'savevote',$this->prefixId.'[uid]' => $this->pollID,'L'=>$GLOBALS['TSFE']->sys_language_content);
				$alink = $this->pi_getPageLink($GLOBALS['TSFE']->id,'',$getParams);
				
				//include captcha
				if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'captcha','sDEF') || $this->conf['captcha']) {
					if (t3lib_extMgm::isLoaded('captcha'))	{
						$subpartArray["###CAPTCHA_IMAGE###"] = '<img src="'.t3lib_extMgm::siteRelPath('captcha').'captcha/captcha.php" alt="Captcha-Code" />';
						$subpartArray["###CAPTCHA_INPUT###"] = '<input type="text" size="8" name="'. $this->prefixId. '[captcha]" value=""/>';
					}
				}
				else {
					$subpartArray["###CAPTCHA_IMAGE###"] = '';
					$subpartArray["###CAPTCHA_INPUT###"] = '';
				}
				
				//include link to list
				if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'list','s_template') || $this->conf['list']) {
					//build url for linklist
					$ll_getParams = array($this->prefixId.'[go]' => 'list');
					$ll_alink = $this->pi_getPageLink($GLOBALS['TSFE']->id,'',$ll_getParams);
					$subpartArray["###LINKLIST###"] = '<a class="jk_poll_linklist" href="'.$ll_alink.'">'.$this->LL_linklist.'</a>';
				}
				else {
					$subpartArray["###LINKLIST###"] = '';
				}
				
				$subpartArray["###SUBMIT###"] = '<input class="pollsubmit" type="submit" value="'.$this->LL_submit_button.'" />';
				$subpartArray["###ANSWER_VOTE###"] = $resultcontentAnswer;
				$content .= $this->cObj->substituteMarkerArrayCached($template["poll_vote"], array(), $subpartArray, array());
	        		$content = '<form name="poll" method="post" action="'. htmlspecialchars($alink). '">'.$content;
				$content .= '</form>';
				
				
			} else {
				//Show result
				$content = $this->showresults();
			}
			
	        return $content;
		}
		else
			return '<div class="error">' .$this->LL_poll_not_visible. '</div>';
	}

	/**
	 * Shows the result of the poll
	 * 
	 * @return	string		HTML to display in the frontend
	 */	
	function showresults() {
		$now = time();
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_jkpoll_poll', 'uid=' .$this->pollID.' AND deleted=0 AND (('.$now.' BETWEEN starttime AND endtime) OR (starttime=0 AND endtime=0)) AND hidden=0 AND sys_language_uid='.$GLOBALS['TSFE']->sys_language_content);
				
		//Get poll data
		if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {

			//Get the votes, answers and colors		
			$votes = explode("\n", $row['votes']);
			//if poll is translation get votes from parent poll
			if ($this->pollID_parent != 0 && (!$this->conf['vote_language_specific'] && !$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'vote_language_specific','language'))) {							
				$res_votes = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_jkpoll_poll', 'uid=' .$this->pollID_parent.' AND deleted=0 AND (('.$now.' BETWEEN starttime AND endtime) OR (starttime=0 AND endtime=0)) AND hidden=0');
				if ($row_votes = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_votes)) {					
					$votes = explode("\n", $row_votes['votes']);
				}
			}
			
			$answers = explode("\n", $row['answers']);
			$colors = explode("\n", $row['colors']);
			$total = 0;
			foreach ($answers as $i => $a) {
				$total += $votes[$i];
			} 
			
			//Get type of poll
			if ($this->conf['type'])
			    $type = $this->conf['type'];
			else
			    $type = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'type','s_template');
			//Get height_width
			$height_width = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'height_width','s_template');
			if ($height_width == "" && $type == 0)
				$height_width = 10;
			elseif ($height_width == "" && $type == 1)
				$height_width = 50;
			//Get factor
			$factor = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'factor','s_template');
			if ($factor == "")
				$factor = 1;
			
			$template = array();
	    		$template['poll_header'] = $this->cObj->getSubpart($this->templateCode,"###POLL_HEADER###"); 
	    		if ($type == 0) 
	        	    $template['answers'] = $this->cObj->getSubpart($this->templateCode,"###POLL_ANSWER_HORIZONTAL###"); 
	    		else
	        	    $template['answers'] = $this->cObj->getSubpart($this->templateCode,"###POLL_ANSWER_VERTICAL###"); 
	    		$template['answer_data'] = $this->cObj->getSubpart($template['answers'],"###ANSWER_RESULT###"); 
			
	    		$markerArrayQuestion = array();
			$markerArrayQuestion["###TITLE###"] = $row['title'];
			$markerArrayQuestion["###QUESTION_IMAGE###"] = $this->getimage($this->pollID);
			$markerArrayQuestion["###QUESTIONTEXT###"] = $this->cObj->stdWrap($row['question'],$this->conf['rtefield_stdWrap.']);
			$content = $this->cObj->substituteMarkerArrayCached($template['poll_header'],$markerArrayQuestion,$subpartArray,$wrappedSubpartArray);
			
			$markerArray["###VOTES_LABEL###"] = $this->LL_votes_label;
			$markerArray["###VOTES###"] = $total;
			$template['answers'] = $this->cObj->substituteMarkerArrayCached($template['answers'],$markerArray,$subpartArray,$wrappedSubpartArray);;
			
			//Get highest result
			$i=0;
			foreach ($votes as $i => $a) {
				if ($total > 0) {
					$percent = round(($votes[$i] / $total)*100,1);
				} else {
					$percent = 0;
				}
				$percents[++$i]=$percent;
			}
			$max=max($percents);
				
			foreach ($answers as $i => $a) {				
				if (trim($colors[$i]) == "")
					if ($this->conf['color'] != '')
						$colors[$i] = $this->conf['color'];
					elseif ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'color','s_template') != '')
						$colors[$i] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'color','s_template');
					else
						$colors[$i]="blue";
				if ($total > 0) {
					$percent = round(($votes[$i] / $total)*100,1);
				} else {
					$percent = 0;
				}				
				
				//Make result bars
				$markerArrayAnswer = array();
				if ($type == 0) 
					$markerArrayAnswer["###IMG_PERCENTAGE_RESULT###"] = '<img src="'.t3lib_extMgm::siteRelPath($this->extKey).'images/'.trim($colors[$i]).'.gif" width="'.$percent*$factor.'" height="'.$height_width.'" alt="'.$percent.'%" />';
					//horizontal
	//				$markerArrayAnswer["###IMG_PERCENTAGE_RESULT###"] = '<div style="float:left; background-color:'.trim($color).'; width:'.$percent*$factor.'px; height:'.$height_width.'px;" title="'.$percent.'%"></div>';
				else 
					$markerArrayAnswer["###IMG_PERCENTAGE_RESULT###"] = '<img src="'.t3lib_extMgm::siteRelPath($this->extKey).'pi1/clear.gif" width="'.$height_width.'" height="'.(($max*$factor)-($percent*$factor)).'" alt="" /><br /><img src="'.t3lib_extMgm::siteRelPath($this->extKey).'images/'.trim($colors[$i]).'.gif" width="'.$height_width.'" height="'.$percent*$factor.'" alt="'.$percent.'%" />';
					// vertical
	//				$markerArrayAnswer["###IMG_PERCENTAGE_RESULT###"] = '<div style="position:absolute; height:'.(100*$factor).'px; width:'.$height_width.'px;"><div style="position:absolute; bottom:0px; background-color:'.trim($color).'; width:'.$height_width.'px; height:'.$percent*$factor.'px;" alt="'.$percent.'%"></div></div>';
				$markerArrayAnswer["###PERCENTAGE_RESULT###"] = $percent." %";
				$markerArrayAnswer["###ANSWERTEXT_RESULT###"] = $answers[$i];
				$markerArrayAnswer["###AMOUNT_VOTES###"] = $votes[$i];
				if ($this->LL_amount_votes_label != '')
					$markerArrayAnswer["###AMOUNT_VOTES_LABEL###"] = $this->LL_amount_votes_label;
				else
					$markerArrayAnswer["###AMOUNT_VOTES_LABEL###"] = $this->LL_votes_label;
				$resultcontentAnswer .= $this->cObj->substituteMarkerArrayCached($template['answer_data'],$markerArrayAnswer,$subpartArray,$wrappedSubpartArray);
			}
		
			$subpartArray["###ANSWER_RESULT###"] = $resultcontentAnswer;
	    		$content .= $this->cObj->substituteMarkerArrayCached($template["answers"], array(), $subpartArray, array());
	    		return $content;
		}
	    else
	    	return '<div class="error">' .$this->LL_poll_not_visible. '</div>';
	}

	/**
     * Saves the votes in the database. Checks cookies to prevent misuse
     *
     * @return   string      HTML to show in frontend
     */
	function savevote() {
		// poll is allowed if cookie not set already
		$cookieName = 't3_tx_jkpoll_'.$this->pollID;
		//Exit if cookie exists		
		if (isset($_COOKIE[$cookieName]))
			return '<div class="error">'. $this->LL_has_voted. '</div>';
			
		if ((!$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'check_language_specific','language') && !$this->conf['check_language_specific']) && $this->pollID_parent != 0)
			$check_poll_id = $this->pollID_parent;
		else
			$check_poll_id = $this->pollID;
			
		//Exit if captcha was not right
		if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'captcha','sDEF') || $this->conf['captcha']) {
			if (t3lib_extMgm::isLoaded('captcha'))	{
				session_start();
				$captchaStr = $_SESSION['tx_captcha_string'];
				$_SESSION['tx_captcha_string'] = '';
			} else {
				$captchaStr = -1;
			}
			if (!($captchaStr===-1 || ($captchaStr && $this->captcha===$captchaStr))) {
				return '<div class="error">' .$this->LL_wrong_captcha. '</div>';
			}
		}
		//Exit if fe_user already voted
		if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'fe_user','sDEF')) {
			if ($GLOBALS['TSFE']->fe_user->user['uid'] != '') {
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_jkpoll_userlog', 'pid='.$check_poll_id.' AND fe_user=\''.$GLOBALS['TSFE']->fe_user->user['uid'].'\'');
				$rows = array();
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					$rows[] = $row;
				}
				if (count($rows))
					return '<div class="error">' .$this->LL_has_voted. '</div>';
			}
		}

		//Exit if IP already logged
		if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'check_ip','sDEF') || $this->conf['check_ip']) {
			//get timestamp after which vote is possible again
			if ($this->conf['check_ip_time'] != "") 
				$vote_time = time() - (intval($this->conf['check_ip_time']) * 3600);
			elseif ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'time','sDEF') != "") 
				$vote_time = time() - (intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'time','sDEF')) * 3600);
			else 
				$vote_time = time();
				
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_jkpoll_iplog', 'pid='.$check_poll_id.' AND ip='.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->REMOTE_ADDR, 'tx_jkpoll_iplog').' AND tstamp >= '.$vote_time);
			$rows = array();
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$rows[] = $row;
			}
			if (count($rows))
				return '<div class="error">' .$this->LL_has_voted. '</div>';
		}
				
		//decide if cookie-path is to be set or not
		if ($this->conf['cookie_domainpath'] == 1)
			$cookiepath = '/';
		
		//decide which type of cookie is to be set
		if (!intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'cookie','sDEF')) && !intval($this->conf['cookie'])) {
			//make non-persistent cookie if "off"
			if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'cookie','sDEF') == "off" || $this->conf['cookie'] == "off") 
				if(!setcookie('t3_tx_jkpoll_'.$check_poll_id,'voted:yes',0,$cookiepath)) 
					return '<div class="error">'. $this->LL_error_no_vote. '</div>';
			//if no value set use 30 days
			if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'cookie','sDEF')==='' || $this->conf['cookie']==='')
				if(!setcookie('t3_tx_jkpoll_'.$check_poll_id,'voted:yes',time() + (3600*24*30),$cookiepath))
					return '<div class="error">'. $this->LL_error_no_vote. '</div>';
		}
		else {
			//delete cookie after time set via flexform
			if (intval($this->conf['cookie']))
			    $cookieTime = time() + (3600*24*intval($this->conf['cookie']));
			else
			    $cookieTime = time() + (3600*24*intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'cookie','sDEF')));
			if(!setcookie('t3_tx_jkpoll_'.$check_poll_id,'voted:yes',$cookieTime,$cookiepath))
				return '<div class="error">'. $this->LL_error_no_vote. '</div>';
		}

		//check if an answer was selected
		if(!intval($this->answer) && $this->answer!='0')
			return '<div class="error">'. $this->LL_error_no_vote_selected. '</div>';
		
		//Get the poll data so it can be updated
		if ($this->pollID_parent != 0 && (!$this->conf['vote_language_specific'] && !$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'vote_language_specific','language')))
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_jkpoll_poll', 'uid=' .$this->pollID_parent);
		else
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_jkpoll_poll', 'uid=' .$this->pollID);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		
		//update number of votes
		$votes = explode("\n", $row['votes']);
		foreach ($votes as $i => $a) {
			//find the answer that was voted for
			if ($i == $this->answer) {
				//update no. of votes
				$a = trim($votes[$i])+1; 
			}
			$newvotes[] = $a;
		}
		
		// write answers back to db
		$dataArr['votes']=implode("\n",$newvotes);
		if ($this->pollID_parent != 0 && (!$this->conf['vote_language_specific'] && !$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'vote_language_specific','language')))
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_jkpoll_poll', 'uid='.$this->pollID_parent, $dataArr);
		else
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_jkpoll_poll', 'uid='.$this->pollID, $dataArr);

		//write IP of voter in db
		if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'check_ip','sDEF') || $this->conf['check_ip']) {
			$insertFields = array(
				'pid' => $check_poll_id,
				'ip' => $this->REMOTE_ADDR,
				'tstamp' => time()
			);
			$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_jkpoll_iplog',$insertFields);
		}
		
		//write FE User in db
		if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'fe_user','sDEF') || $this->conf['check_user']) {
			$insertFields = array(
				'pid' => $check_poll_id,
				'fe_user' => $GLOBALS['TSFE']->fe_user->user['uid'],
				'tstamp' => time()
			);
			$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_jkpoll_userlog',$insertFields);
		}
		
		//Show the poll results or forward to page specified
		if ($this->conf['PIDforward'])
			header('Location:'.t3lib_div::locationHeaderUrl($this->conf['PIDforward']),'',array('L'=>$GLOBALS['TSFE']->sys_language_content));
		elseif ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'PIDforward','s_template'))
			header('Location:'.t3lib_div::locationHeaderUrl($this->pi_getPageLink($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'PIDforward','s_template'),'',array('L'=>$GLOBALS['TSFE']->sys_language_content))));
		else				
			$content = $this->showresults();		

		return $content;
		
	}
	
	/**
     * Gets the newest active poll on the page / startingpoint page or the one specified via GET
     *
     * @return   boolean      pollID was found and set or not
     */
	function getPollID () {
		
		//The id of the page with the poll to use. Take from template, or the starting point page or
		//by default use current page
		if ($this->conf['pid'])
			$this->pid = $this->conf['pid'];
		else
			$this->pid = intval($this->cObj->data['pages'] ? $this->cObj->data['pages'] : $GLOBALS['TSFE']->id);
		
		//Get the poll id from parameter or select newest active poll (only newest poll is voteable)	
		if ($this->piVars['uid'] != "") {			
			if (!$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'vote_old','sDEF')) {
				if ($this->piVars['uid'] == $this->getLastPoll()) 
					$this->voteable = 1;
				else 
					$this->voteable = 0;
				$this->pollID = intval($this->piVars['uid']);
				//check if poll is translated
				$this->pollID_parent = $this->getPollIDParent($this->pollID);
			}
			else {
				$this->pollID = intval($this->piVars['uid']);
				$this->voteable = 1;
				$this->pollID_parent = $this->getPollIDParent($this->pollID);		 
			}
		}		
		//Get the last poll from storage page
		else {
			$this->pollID = $this->getLastPoll();
			$this->pollID_parent = $this->getPollIDParent($this->pollID);
			//return false if no poll found
			if (!$this->pollID) {
				return false;
			}
			
			$this->voteable = 1;
		}
		
		
		$now = time();
		//check if poll is available for language selected
		$res_poll = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_jkpoll_poll', 'uid=' .$this->pollID.' AND deleted=0 AND (('.$now.' BETWEEN starttime AND endtime) OR (starttime=0 AND endtime=0)) AND hidden=0 AND sys_language_uid='.$GLOBALS['TSFE']->sys_language_content);
		if ($row_poll = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_poll))
			$poll_available = true;
		else
			$poll_available = false;
		
		if($GLOBALS['TSFE']->sys_language_content != '0' && !$poll_available) {			
			$res_language = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_jkpoll_poll', 'l18n_parent=' .$this->pollID.' AND deleted=0 AND (('.$now.' BETWEEN starttime AND endtime) OR (starttime=0 AND endtime=0)) AND hidden=0 AND sys_language_uid='.$GLOBALS['TSFE']->sys_language_content);
			if ($row_language = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_language)) {
				$this->pollID = $row_language['uid'];
			}
		}
		elseif (!$poll_available) {
			$res_language = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_jkpoll_poll', 'uid=' .$this->pollID.' AND deleted=0 AND (('.$now.' BETWEEN starttime AND endtime) OR (starttime=0 AND endtime=0)) AND hidden=0');
			if ($row_language = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_language)) {
				$this->pollID = $row_language['l18n_parent'];
			}
		}
		
		return true;
	}
	
	
	/**
	 * Gets the parent uid of the poll if translated
	 * 
	 * @param	integer		$uid : uid of poll which should be checked for parent uid
	 * @return	integer		parent uid of poll (0 if none found)
	 */	
	function getPollIDParent($uid) {
		$now = time();		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_jkpoll_poll', 'pid=' .$this->pid. ' AND uid='.$uid.' AND deleted=0 AND (('.$now.' BETWEEN starttime AND endtime) OR (starttime=0 AND endtime=0)) AND hidden=0 ORDER BY crdate DESC');
		if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if ($row['l18n_parent'] != 0)
				return $row['l18n_parent'];
			else
				return 0;
			echo $row['l18n_parent'];
		}
	}
	
	
	/**
     *  Gets the newest active poll on the page / startingpoint page and returns its ID
     *
     * @return   string      uid of the last active poll on the page / startingpoint
     */	
	function getLastPoll () {
		
		//Get the last poll from storage page
		$now = time();

		//Find any poll records on the chosen page.
		//Polls that are not hidden or deleted and that are active according to start and end date
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,l18n_parent', 'tx_jkpoll_poll', 'pid=' .$this->pid. ' AND deleted=0 AND (('.$now.' BETWEEN starttime AND endtime) OR (starttime=0 AND endtime=0)) AND hidden=0 AND sys_language_uid='.$GLOBALS['TSFE']->sys_language_content.' ORDER BY crdate DESC');

		//return false if no poll found
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) == 0) {
			return false;
		}
		else {
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			if ($row['l18n_parent'] != 0)
				$this->pollID_parent = $row['l18n_parent']; 			
			return $row['uid'];
		}
	}
	
	
	/**
     *  Shows a list of all polls
     *
     * @return   string      HTML list of all polls
     */
	function showlist() {
		
		//The id of the page with the poll to use. Take from the starting point page or
		//by default use current page
		if ($this->conf['pid'])
		    $this->pid = $this->conf['pid'];
		else
		    $this->pid = intval($this->cObj->data['pages'] ? $this->cObj->data['pages'] : $GLOBALS['TSFE']->id);
		
		//Get the page where the poll is located
		if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'PIDitemDisplay','s_template') != "")
			$id = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'PIDitemDisplay','s_template');
		else 
			$id=$GLOBALS["TSFE"]->id;
			
		$now = time();
		
		//Get the amount of polls that should be displayed
		if (intval($this->conf['list_limit']))
			$limit = intval($this->conf['list_limit']);
		elseif ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'amount','sDEF') != "")
			$limit = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'amount','sDEF');
		else 
			$limit='';
				
		//Find any poll records on the chosen page. 
		//Polls that are not hidden or deleted and that are active according to start and end date
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, title','tx_jkpoll_poll','pid='.$this->pid.' AND deleted=0 AND (('.$now.' BETWEEN starttime AND endtime) OR (starttime=0 AND endtime=0)) AND hidden=0 AND sys_language_uid='.$GLOBALS['TSFE']->sys_language_content,'','crdate DESC',$limit);
		
		$template['poll_list'] = $this->cObj->getSubpart($this->templateCode,"###POLL_LIST###"); 
        $template['link'] = $this->cObj->getSubpart($template['poll_list'],"###POLL_LINK###"); 
		
        //show first poll in list?
        if (!$this->pi_getFFvalue($this->cObj->data['pi_flexform'],'show_first','sDEF') && !$this->conf['list_first'] ) 
        	$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$markerArray = array();
			$markerArray["###LINK###"] = $this->pi_linkToPage($row['title'], $id,"",array($this->prefixId."[uid]"=>$row['uid']));
			$content_tmp .= $this->cObj->substituteMarkerArrayCached($template['link'],$markerArray, array(), array());
        }
        
        $subpartArray = array();
		$subpartArray["###POLL_LINK###"] = $content_tmp;
		$content .= $this->cObj->substituteMarkerArrayCached($template['poll_list'], array(), $subpartArray, array());
	
        return $content;
	}

	/**
	 * Returns the HTML for the image
	 * 
	 * @param	integer		$uid : uid of poll	 
	 * @return	integer		HTML for the image
	 */
	function getimage($uid) {
		
		//Get poll data
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_jkpoll_poll', 'uid=' .$uid);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		
		if ($this->pollID_parent != 0) {
			$res_parent = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_jkpoll_poll', 'uid=' .$this->pollID_parent);
			$row_parent = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_parent);
			
			$imgTSConfig["file"] = "uploads/tx_jkpoll/".$row_parent["image"];
			$imgTSConfig['altText']   = $row["alternative_tag"];
  			$imgTSConfig['titleText'] = $row["title_tag"];
  			$link = $row["link"];
  			$width = $row_parent["width"];
  			$height = $row_parent["height"];
  			$clickenlarge = $row_parent["clickenlarge"];
		}
		else {
			$imgTSConfig["file"] = "uploads/tx_jkpoll/".$row["image"];
			$imgTSConfig['altText']   = $row["alternative_tag"];
  			$imgTSConfig['titleText'] = $row["title_tag"];
  			$link = $row["link"];
  			$width = $row["width"];
  			$height = $row["height"];
  			$clickenlarge = $row["clickenlarge"];
		}
						
  		if ($width)
  			$imgTSConfig["file."]['width'] = $width;
  		if ($height)
  			$imgTSConfig["file."]['height'] = $height;
  		if ($clickenlarge) {
  			$imgTSConfig['imageLinkWrap'] = 1;
  			$imgTSConfig['imageLinkWrap.']['JSwindow'] = 1;
  			$imgTSConfig['imageLinkWrap.']['bodyTag'] = '<body bgcolor="black">';
  			$imgTSConfig['imageLinkWrap.']['JSwindow.']['newWindow'] = 0;
  			$imgTSConfig['imageLinkWrap.']['JSwindow.']['expand'] = '17,20';
  			$imgTSConfig['imageLinkWrap.']['enable'] = 1;
  			$imgTSConfig['imageLinkWrap.']['wrap'] = '<a href="javascript:close();"> | </a>';
  			$imgTSConfig['imageLinkWrap.']['width'] = 800;
  			$imgTSConfig['imageLinkWrap.']['height'] = 600;	
  		}
  		if ($link && !$clickenlarge) {
  			$imgTSConfig['imageLinkWrap'] = 1;
  			$imgTSConfig['imageLinkWrap.']['enable'] = 1;
  			$imgTSConfig['imageLinkWrap.']['typolink.']['parameter'] = $link;
  		}
		return $this->cObj->IMAGE($imgTSConfig);
	}
	
}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/jk_poll/pi1/class.tx_jkpoll_pi1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/jk_poll/pi1/class.tx_jkpoll_pi1.php"]);
}

?>