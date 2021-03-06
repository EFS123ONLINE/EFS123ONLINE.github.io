<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Block/classes/class.ilBlockGUI.php");

/**
* BlockGUI class for wiki functions block
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Is+++CalledBy ilWikiSideBlockGUI: ilColumnGUI
*
* @ingroup ModulesWiki
*/
class ilWikiFunctionsBlockGUI extends ilBlockGUI
{
	static $block_type = "wikiside";
	static $st_data;
	
	/**
	* Constructor
	*/
	function __construct()
	{
		global $ilCtrl, $lng;
		
		parent::ilBlockGUI();
		
		//$this->setImage(ilUtil::getImagePath("icon_news_s.png"));

		$lng->loadLanguageModule("wiki");
		//$this->setBlockId(...);
		/*$this->setLimit(5);
		$this->setAvailableDetailLevels(3);*/
		$this->setEnableNumInfo(false);
		
		$this->setTitle($lng->txt("wiki_functions"));
		//$this->setRowTemplate("tpl.block_row_news_for_context.html", "Services/News");
		//$this->setData($data);
		$this->allow_moving = false;
		//$this->handleView();
	}

	/**
	* Get block type
	*
	* @return	string	Block type.
	*/
	static function getBlockType()
	{
		return self::$block_type;
	}

	/**
	* Is this a repository object
	*
	* @return	string	Block type.
	*/
	static function isRepositoryObject()
	{
		return false;
	}
	
	/**
	* Get Screen Mode for current command.
	*/
	static function getScreenMode()
	{
		return IL_SCREEN_SIDE;
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd("getHTML");

		switch ($next_class)
		{
			default:
				return $this->$cmd();
		}
	}

	/**
	* Set Page Object
	*
	* @param	int	$a_pageob	Page Object
	*/
	function setPageObject($a_pageob)
	{
		$this->pageob = $a_pageob;
	}

	/**
	* Get Page Object
	*
	* @return	int	Page Object
	*/
	function getPageObject()
	{
		return $this->pageob;
	}

	/**
	* Get bloch HTML code.
	*/
	function getHTML()
	{
		global $ilCtrl, $lng, $ilUser;
		
		return parent::getHTML();
	}

	/**
	* Fill data section
	*/
	function fillDataSection()
	{
		global $ilCtrl, $lng, $ilAccess;
		
		$tpl = new ilTemplate("tpl.wiki_side_block_content.html", true, true, "Modules/Wiki");
		
		$wp = $this->getPageObject();

		// info
		$actions[] = array(
			"txt" => $lng->txt("info_short"),
			"href" => $ilCtrl->getLinkTargetByClass("ilobjwikigui", "infoScreen")
			);

		// recent changes
		$actions[] = array(
			"txt" => $lng->txt("wiki_recent_changes"),
			"href" => $ilCtrl->getLinkTargetByClass("ilobjwikigui", "recentChanges")
			);

		foreach ($actions as $a)
		{
			$tpl->setCurrentBlock("action");
			$tpl->setVariable("HREF", $a["href"]);
			$tpl->setVariable("TXT", $a["txt"]);
			$tpl->parseCurrentBlock();

			$tpl->touchBlock("item");
		}


		$actions = array();
		
		// all pages
		$actions[] = array(
			"txt" => $lng->txt("wiki_all_pages"),
			"href" => $ilCtrl->getLinkTargetByClass("ilobjwikigui", "allPages")
			);

		// new pages
		$actions[] = array(
			"txt" => $lng->txt("wiki_new_pages"),
			"href" => $ilCtrl->getLinkTargetByClass("ilobjwikigui", "newPages")
			);

		// popular pages
		$actions[] = array(
			"txt" => $lng->txt("wiki_popular_pages"),
			"href" => $ilCtrl->getLinkTargetByClass("ilobjwikigui", "popularPages")
			);

		// orphaned pages
		$actions[] = array(
			"txt" => $lng->txt("wiki_orphaned_pages"),
			"href" => $ilCtrl->getLinkTargetByClass("ilobjwikigui", "orphanedPages")
			);


		// page lists
		include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		$list = new ilAdvancedSelectionListGUI();
		$list->setListTitle($lng->txt("wiki_page_lists"));
		$list->setId("wiki_pglists");

		foreach ($actions as $a)
		{
			$list->addItem($a["txt"], "",
				$a["href"]);
		}
		$tpl->setCurrentBlock("plain");
		$tpl->setVariable("PLAIN", $list->getHTML());
		$tpl->parseCurrentBlock();
		$tpl->touchBlock("item");

		
		// page actions
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			$list = new ilAdvancedSelectionListGUI();
			$list->setListTitle($lng->txt("wiki_page_actions"));
			$list->setId("wiki_pgactions");
			
			// rating
			if(ilObjWiki::_lookupRating($this->getPageObject()->getWikiId()))
			{			
				if(!$this->getPageObject()->getRating())
				{
					$list->addItem($lng->txt("wiki_activate_page_rating"), "",
						$ilCtrl->getLinkTargetByClass("ilwikipagegui", "activateWikiPageRating"));
				}
				else
				{			
					$list->addItem($lng->txt("wiki_deactivate_page_rating"), "",
						$ilCtrl->getLinkTargetByClass("ilwikipagegui", "deactivateWikiPageRating"));
				}
			}

			// rename
			$list->addItem($lng->txt("wiki_rename_page"), "",
				$ilCtrl->getLinkTargetByClass("ilwikipagegui", "renameWikiPage"));

			// block/unblock
			if ($this->getPageObject()->getBlocked())
			{
				$list->addItem($lng->txt("wiki_unblock_page"), "",
					$ilCtrl->getLinkTargetByClass("ilwikipagegui", "unblockWikiPage"));
			}
			else
			{
				$list->addItem($lng->txt("wiki_block_page"), "",
					$ilCtrl->getLinkTargetByClass("ilwikipagegui", "blockWikiPage"));
			}

			// delete page
			$st_page = ilObjWiki::_lookupStartPage($this->getPageObject()->getParentId());
			if ($st_page != $this->getPageObject()->getTitle())
			{
				$list->addItem($lng->txt("wiki_delete_page"), "",
					$ilCtrl->getLinkTargetByClass("ilwikipagegui", "deleteWikiPageConfirmationScreen"));
			}
									
			$tpl->setCurrentBlock("plain");
			$tpl->setVariable("PLAIN", $list->getHTML());
			$tpl->parseCurrentBlock();
			$tpl->touchBlock("item");
		}

		
		// permissions
//		if ($ilAccess->checkAccess('edit_permission', "", $_GET["ref_id"]))
//		{
//			$actions[] = array(
//				"txt" => $lng->txt("perm_settings"),
//				"href" => $ilCtrl->getLinkTargetByClass(array("ilobjwikigui", "ilpermissiongui"), "perm")
//				);
//		}

		$actions = array();
		
		// settings
		if ($ilAccess->checkAccess('write', "", $_GET["ref_id"]))
		{
//			$actions[] = array(
//				"txt" => $lng->txt("settings"),
//				"href" => $ilCtrl->getLinkTargetByClass("ilobjwikigui", "editSettings")
//				);
			$actions[] = array(
				"txt" => $lng->txt("wiki_contributors"),
				"href" => $ilCtrl->getLinkTargetByClass("ilobjwikigui", "listContributors")
				);
		}

		// manage
		if ($ilAccess->checkAccess('write', "", $_GET["ref_id"]))
		{
			$actions[] = array(
				"txt" => $lng->txt("settings"),
				"href" => $ilCtrl->getLinkTargetByClass("ilobjwikigui", "editSettings")
				);
		}

		foreach ($actions as $a)
		{
			$tpl->setCurrentBlock("action");
			$tpl->setVariable("HREF", $a["href"]);
			$tpl->setVariable("TXT", $a["txt"]);
			$tpl->parseCurrentBlock();

			$tpl->touchBlock("item");
		}


		$this->setDataSection($tpl->get());
	}
}

?>
