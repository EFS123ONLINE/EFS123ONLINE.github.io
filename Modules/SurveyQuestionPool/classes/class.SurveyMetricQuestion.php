<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
include_once "./Modules/Survey/classes/inc.SurveyConstants.php";

define("SUBTYPE_NON_RATIO", 3);
define("SUBTYPE_RATIO_NON_ABSOLUTE", 4);
define("SUBTYPE_RATIO_ABSOLUTE", 5);

/**
* Metric survey question
*
* The SurveyMetricQuestion class defines and encapsulates basic methods and attributes
* for metric survey question types.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @extends SurveyQuestion
* @ingroup ModulesSurveyQuestionPool
*/
class SurveyMetricQuestion extends SurveyQuestion 
{
/**
* Question subtype
*
* A question subtype (Multiple choice single response or multiple choice multiple response)
*
* @var integer
*/
  var $subtype;

/**
* The minimum value for the metric question
*
* @var double
*/
  var $minimum;

/**
* The maximum value for the metric question
*
* @var double
*/
  var $maximum;

/**
* SurveyMetricQuestion constructor
*
* The constructor takes possible arguments an creates an instance of the SurveyMetricQuestion object.
*
* @param string $title A title string to describe the question
* @param string $description A description string to describe the question
* @param string $author A string containing the name of the questions author
* @param integer $owner A numerical ID to identify the owner/creator
* @access public
*/
	function SurveyMetricQuestion(
		$title = "",
		$description = "",
		$author = "",
		$questiontext = "",
		$owner = -1,
		$subtype = SUBTYPE_NON_RATIO
	)
	{
		$this->SurveyQuestion($title, $description, $author, $questiontext, $owner);
		$this->subtype = $subtype;
		$this->minimum = "";
		$this->maximum = "";
	}
	
/**
* Sets the question subtype
*
* @param integer $subtype The question subtype
* @access public
* @see $subtype
*/
  function setSubtype($subtype = SUBTYPE_NON_RATIO) 
	{
    $this->subtype = $subtype;
  }

/**
* Sets the minimum value
*
* @param double $minimum The minimum value
* @access public
* @see $minimum
*/
  function setMinimum($minimum = 0) 
	{
	   if($minimum !== NULL)
	  {
		  $minimum = (float)$minimum;		 
	  }
	   if(!$minimum)
		{
			$minimum = NULL;
		}
    $this->minimum = $minimum;
  }

/**
* Sets the maximum value
*
* @param double $maximum The maximum value
* @access public
* @see $maximum
*/
  function setMaximum($maximum = "") 
	{
	    if($maximum !== NULL)
	  {
		  $maximum = (float)$maximum;
	  }
	   if(!$maximum)
		{
			$maximum = NULL;
		}
    $this->maximum = $maximum;
  }

/**
* Gets the question subtype
*
* @return integer The question subtype
* @access public
* @see $subtype
*/
  function getSubtype() 
	{
    return $this->subtype;
  }
	
/**
* Returns the minimum value of the question
*
* @return double The minimum value of the question
* @access public
* @see $minimum
*/
	function getMinimum() 
	{
		if ((strlen($this->minimum) == 0) && ($this->getSubtype() > 3))
		{
			$this->minimum = 0;
		}
		return (strlen($this->minimum)) ? $this->minimum : NULL;
	}
	
/**
* Returns the maximum value of the question
*
* @return double The maximum value of the question
* @access public
* @see $maximum
*/
	function getMaximum() 
	{
		return (strlen($this->maximum)) ? $this->maximum : NULL;
	}
	
	/**
	* Returns the question data fields from the database
	*
	* @param integer $id The question ID from the database
	* @return array Array containing the question fields and data from the database
	* @access public
	*/
	function _getQuestionDataArray($id)
	{
		global $ilDB;
		
		$result = $ilDB->queryF("SELECT svy_question.*, " . $this->getAdditionalTableName() . ".* FROM svy_question, " . $this->getAdditionalTableName() . " WHERE svy_question.question_id = %s AND svy_question.question_id = " . $this->getAdditionalTableName() . ".question_fi",
			array('integer'),
			array($id)
		);
		if ($result->numRows() == 1)
		{
			return $ilDB->fetchAssoc($result);
		}
		else
		{
			return array();
		}
	}
	
/**
* Loads a SurveyMetricQuestion object from the database
*
* @param integer $id The database id of the metric survey question
* @access public
*/
	function loadFromDb($id) 
	{
		global $ilDB;

		$result = $ilDB->queryF("SELECT svy_question.*, " . $this->getAdditionalTableName() . ".* FROM svy_question LEFT JOIN " . $this->getAdditionalTableName() . " ON " . $this->getAdditionalTableName() . ".question_fi = svy_question.question_id WHERE svy_question.question_id = %s",
			array('integer'),
			array($id)
		);
		if ($result->numRows() == 1) 
		{
			$data = $ilDB->fetchAssoc($result);
			$this->setId($data["question_id"]);
			$this->setTitle($data["title"]);
			$this->setDescription($data["description"]);
			$this->setObjId($data["obj_fi"]);
			$this->setAuthor($data["author"]);
			$this->setOwner($data["owner_fi"]);
			$this->label = $data['label'];
			include_once("./Services/RTE/classes/class.ilRTE.php");
			$this->setQuestiontext(ilRTE::_replaceMediaObjectImageSrc($data["questiontext"], 1));
			$this->setObligatory($data["obligatory"]);
			$this->setComplete($data["complete"]);
			$this->setOriginalId($data["original_id"]);
			$this->setSubtype($data["subtype"]);

			$result = $ilDB->queryF("SELECT svy_variable.* FROM svy_variable WHERE svy_variable.question_fi = %s",
				array('integer'),
				array($id)
			);
			if ($result->numRows() > 0) 
			{
				if ($data = $ilDB->fetchAssoc($result)) 
				{
					$this->minimum = $data["value1"];
					if (($data["value2"] < 0) or (strcmp($data["value2"], "") == 0))
					{
						$this->maximum = "";
					}
					else
					{
						$this->maximum = $data["value2"];
					}
				}
			}
		}
		parent::loadFromDb($id);
	}

/**
* Returns true if the question is complete for use
*
* @result boolean True if the question is complete for use, otherwise false
* @access public
*/
	function isComplete()
	{
		if (
			strlen($this->getTitle()) &&
			strlen($this->getAuthor()) &&
			strlen($this->getQuestiontext())
		)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	
/**
* Saves a SurveyMetricQuestion object to a database
*
* @access public
*/
  function saveToDb($original_id = "")
  {
		global $ilDB;

		$affectedRows = parent::saveToDb($original_id);
		if ($affectedRows == 1) 
		{
			$affectedRows = $ilDB->manipulateF("DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s",
				array('integer'),
				array($this->getId())
			);
			$affectedRows = $ilDB->manipulateF("INSERT INTO " . $this->getAdditionalTableName() . " (question_fi, subtype) VALUES (%s, %s)",
				array('integer', 'text'),
				array($this->getId(), $this->getSubType())
			);

			// saving material uris in the database
			$this->saveMaterial();
			
			// save categories
			$affectedRows = $ilDB->manipulateF("DELETE FROM svy_variable WHERE question_fi = %s",
				array('integer'),
				array($this->getId())
			);

			if (preg_match("/[\D]/", $this->maximum) or (strcmp($this->maximum, "&infin;") == 0))
			{
				$max = -1;
			}
			else
			{
				$max = $this->getMaximum();
			}
			$next_id = $ilDB->nextId('svy_variable');
			$affectedRows = $ilDB->manipulateF("INSERT INTO svy_variable (variable_id, category_fi, question_fi, value1, value2, sequence, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s)",
				array('integer','integer','integer','float','float','integer','integer'),
				array($next_id, 0, $this->getId(), $this->getMinimum(), $max, 0, time())
			);
		} 
  }
	
	/**
	* Returns an xml representation of the question
	*
	* @return string The xml representation of the question
	* @access public
	*/
	function toXML($a_include_header = TRUE, $obligatory_state = "")
	{
		include_once("./Services/Xml/classes/class.ilXmlWriter.php");
		$a_xml_writer = new ilXmlWriter;
		$a_xml_writer->xmlHeader();
		$this->insertXML($a_xml_writer, $a_include_header, $obligatory_state);
		$xml = $a_xml_writer->xmlDumpMem(FALSE);
		if (!$a_include_header)
		{
			$pos = strpos($xml, "?>");
			$xml = substr($xml, $pos + 2);
		}
		return $xml;
	}
	
	/**
	* Adds the question XML to a given XMLWriter object
	*
	* @param object $a_xml_writer The XMLWriter object
	* @param boolean $a_include_header Determines wheather or not the XML should be used
	* @param string $obligatory_state The value of the obligatory state
	* @access public
	*/
	function insertXML(&$a_xml_writer, $a_include_header = TRUE, $obligatory_state = "")
	{
		$attrs = array(
			"id" => $this->getId(),
			"title" => $this->getTitle(),
			"type" => $this->getQuestiontype(),
			"subtype" => $this->getSubtype(),
			"obligatory" => $this->getObligatory()
		);
		$a_xml_writer->xmlStartTag("question", $attrs);
		
		$a_xml_writer->xmlElement("description", NULL, $this->getDescription());
		$a_xml_writer->xmlElement("author", NULL, $this->getAuthor());
		$a_xml_writer->xmlStartTag("questiontext");
		$this->addMaterialTag($a_xml_writer, $this->getQuestiontext());
		$a_xml_writer->xmlEndTag("questiontext");

		$a_xml_writer->xmlStartTag("responses");
		switch ($this->getSubtype())
		{
			case 3:
				$attrs = array(
					"id" => "0",
					"format" => "double"
				);
				if (strlen($this->getMinimum()))
				{
					$attrs["min"] = $this->getMinimum();
				}
				if (strlen($this->getMaximum()))
				{
					$attrs["max"] = $this->getMaximum();
				}
				break;
			case 4:
				$attrs = array(
					"id" => "0",
					"format" => "double"
				);
				if (strlen($this->getMinimum()))
				{
					$attrs["min"] = $this->getMinimum();
				}
				if (strlen($this->getMaximum()))
				{
					$attrs["max"] = $this->getMaximum();
				}
				break;
			case 5:
				$attrs = array(
					"id" => "0",
					"format" => "integer"
				);
				if (strlen($this->getMinimum()))
				{
					$attrs["min"] = $this->getMinimum();
				}
				if (strlen($this->getMaximum()))
				{
					$attrs["max"] = $this->getMaximum();
				}
				break;
		}
		$a_xml_writer->xmlStartTag("response_num", $attrs);
		$a_xml_writer->xmlEndTag("response_num");

		$a_xml_writer->xmlEndTag("responses");

		if (count($this->material))
		{
			if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $this->material["internal_link"], $matches))
			{
				$attrs = array(
					"label" => $this->material["title"]
				);
				$a_xml_writer->xmlStartTag("material", $attrs);
				$intlink = "il_" . IL_INST_ID . "_" . $matches[2] . "_" . $matches[3];
				if (strcmp($matches[1], "") != 0)
				{
					$intlink = $this->material["internal_link"];
				}
				$a_xml_writer->xmlElement("mattext", NULL, $intlink);
				$a_xml_writer->xmlEndTag("material");
			}
		}
		
		$a_xml_writer->xmlEndTag("question");
	}

	/**
	* Returns the question type ID of the question
	*
	* @return integer The question type of the question
	* @access public
	*/
	function getQuestionTypeID()
	{
		global $ilDB;
		$result = $ilDB->queryF("SELECT questiontype_id FROM svy_qtype WHERE type_tag = %s",
			array('text'),
			array($this->getQuestionType())
		);
		$row = $ilDB->fetchAssoc($result);
		return $row["questiontype_id"];
	}

	/**
	* Returns the question type of the question
	*
	* @return integer The question type of the question
	* @access public
	*/
	function getQuestionType()
	{
		return "SurveyMetricQuestion";
	}
	
	/**
	* Returns the name of the additional question data table in the database
	*
	* @return string The additional table name
	* @access public
	*/
	function getAdditionalTableName()
	{
		return "svy_qst_metric";
	}
	
	/**
	* Creates the user data of the svy_answer table from the POST data
	*
	* @return array User data according to the svy_answer table
	* @access public
	*/
	function &getWorkingDataFromUserInput($post_data)
	{
		$entered_value = $post_data[$this->getId() . "_metric_question"];
		$data = array();
		if (strlen($entered_value))
		{
			array_push($data, array("value" => $entered_value));
		}
		return $data;
	}
	
	/**
	* Checks the input of the active user for obligatory status
	* and entered values
	*
	* @param array $post_data The contents of the $_POST array
	* @param integer $survey_id The database ID of the active survey
	* @return string Empty string if the input is ok, an error message otherwise
	* @access public
	*/
	function checkUserInput($post_data, $survey_id)
	{
		$entered_value = $post_data[$this->getId() . "_metric_question"];
		// replace german notation with international notation
		$entered_value = str_replace(",", ".", $entered_value);
		
		if ((!$this->getObligatory($survey_id)) && (strlen($entered_value) == 0)) return "";
		
		if (strlen($entered_value) == 0) return $this->lng->txt("survey_question_obligatory");
		
		if (strlen($this->getMinimum()))
		{
			if ($entered_value < $this->getMinimum())
			{
				return $this->lng->txt("metric_question_out_of_bounds");
			}
		}

		if (strlen($this->getMaximum()))
		{
			if (($this->getMaximum() == 1) && ($this->getMaximum() < $this->getMinimum()))
			{
				// old &infty; values as maximum
			}
			else
			{
				if ($entered_value > $this->getMaximum())
				{
					return $this->lng->txt("metric_question_out_of_bounds");
				}
			}
		}

		if (!is_numeric($entered_value))
		{
			return $this->lng->txt("metric_question_not_a_value");
		}

		if (($this->getSubType() == SUBTYPE_RATIO_ABSOLUTE) && (intval($entered_value) != doubleval($entered_value)))
		{
			return $this->lng->txt("metric_question_floating_point");
		}
		return "";
	}
	
	/**
	* Saves random answers for a given active user in the database
	*
	* @param integer $active_id The database ID of the active user
	*/
	public function saveRandomData($active_id)
	{
		global $ilDB;
		// single response
		$number = rand($this->getMinimum(), (strlen($this->getMaximum())) ? $this->getMaximum() : 100);
		$next_id = $ilDB->nextId('svy_answer');
		$affectedRows = $ilDB->manipulateF("INSERT INTO svy_answer (answer_id, question_fi, active_fi, value, textanswer, tstamp) VALUES (%s, %s, %s, %s, %s, %s)",
			array('integer','integer','integer','float','text','integer'),
			array($next_id, $this->getId(), $active_id, $number, NULL, time())
		);
	}

	function saveUserInput($post_data, $active_id, $a_return = false)
	{
		global $ilDB;
		
		$entered_value = $post_data[$this->getId() . "_metric_question"];
		
		// replace german notation with international notation
		$entered_value = str_replace(",", ".", $entered_value);
		
		if($a_return)
		{
			return array(array("value"=>$entered_value, "textanswer"=>null));
		}
		if (strlen($entered_value) == 0) return;
		
		$next_id = $ilDB->nextId('svy_answer');
		$affectedRows = $ilDB->manipulateF("INSERT INTO svy_answer (answer_id, question_fi, active_fi, value, textanswer, tstamp) VALUES (%s, %s, %s, %s, %s, %s)",
			array('integer','integer','integer','float','text','integer'),
			array($next_id, $this->getId(), $active_id, (strlen($entered_value)) ? $entered_value : NULL, NULL, time())
		);
	}
	
	function &getCumulatedResults($survey_id, $nr_of_users)
	{
		global $ilDB;
		
		$question_id = $this->getId();
		
		$result_array = array();
		$cumulated = array();

		$result = $ilDB->queryF("SELECT svy_answer.* FROM svy_answer, svy_finished WHERE svy_answer.question_fi = %s AND svy_finished.survey_fi = %s AND svy_finished.finished_id = svy_answer.active_fi",
			array('integer', 'integer'),
			array($question_id, $survey_id)
		);
		
		while ($row = $ilDB->fetchAssoc($result))
		{
			$cumulated[$row["value"]]++;
		}
		asort($cumulated, SORT_NUMERIC);
		end($cumulated);
		$numrows = $result->numRows();
		$result_array["USERS_ANSWERED"] = $result->numRows();
		$result_array["USERS_SKIPPED"] = $nr_of_users - $result->numRows();
		$result_array["MODE"] = key($cumulated);
		$result_array["MODE_VALUE"] = key($cumulated);
		$result_array["MODE_NR_OF_SELECTIONS"] = $cumulated[key($cumulated)];
		ksort($cumulated, SORT_NUMERIC);
		$counter = 0;
		foreach ($cumulated as $value => $nr_of_users)
		{
			$percentage = 0;
			if ($numrows > 0)
			{
				$percentage = (float)($nr_of_users/$numrows);
			}
			$result_array["values"][$counter++] = array("value" => $value, "selected" => (int)$nr_of_users, "percentage" => $percentage);
		}
		$median = array();
		$total = 0;
		$x_i = 0;
		$p_i = 1;
		$x_i_inv = 0;
		$sum_part_zero = false;
		foreach ($cumulated as $value => $key)
		{
			$total += $key;
			for ($i = 0; $i < $key; $i++)
			{
				array_push($median, $value);
				$x_i += $value;
				$p_i *= $value;
				if ($value != 0)
				{
					$sum_part_zero = true;
					$x_i_inv += 1/$value;
				}
			}
		}
		if ($total > 0)
		{
			if (($total % 2) == 0)
			{
				$median_value = 0.5 * ($median[($total/2)-1] + $median[($total/2)]);
			}
			else
			{
				$median_value = $median[(($total+1)/2)-1];
			}
		}
		else
		{
			$median_value = "";
		}
		if ($total > 0)
		{
			if (($x_i/$total) == (int)($x_i/$total))
			{
				$result_array["ARITHMETIC_MEAN"] = $x_i/$total;
			}
			else
			{
				$result_array["ARITHMETIC_MEAN"] = sprintf("%.2f", $x_i/$total);
			}
		}
		else
		{
			$result_array["ARITHMETIC_MEAN"] = "";
		}
		$result_array["MEDIAN"] = $median_value;
		$result_array["QUESTION_TYPE"] = "SurveyMetricQuestion";
		return $result_array;
	}
	
	/**
	* Creates an Excel worksheet for the detailed cumulated results of this question
	*
	* @param object $workbook Reference to the parent excel workbook
	* @param object $format_title Excel title format
	* @param object $format_bold Excel bold format
	* @param array $eval_data Cumulated evaluation data
	* @access public
	*/
	function setExportDetailsXLS(&$workbook, &$format_title, &$format_bold, &$eval_data, $export_label)
	{
		include_once ("./Services/Excel/classes/class.ilExcelUtils.php");
		$worksheet =& $workbook->addWorksheet();
		$rowcounter = 0;
		switch ($export_label)
		{
			case 'label_only':
				$worksheet->writeString(0, 0, ilExcelUtils::_convert_text($this->lng->txt("label")), $format_bold);
				$worksheet->writeString(0, 1, ilExcelUtils::_convert_text($this->label));
				break;
			case 'title_only':
				$worksheet->writeString(0, 0, ilExcelUtils::_convert_text($this->lng->txt("title")), $format_bold);
				$worksheet->writeString(0, 1, ilExcelUtils::_convert_text($this->getTitle()));
				break;
			default:
				$worksheet->writeString(0, 0, ilExcelUtils::_convert_text($this->lng->txt("title")), $format_bold);
				$worksheet->writeString(0, 1, ilExcelUtils::_convert_text($this->getTitle()));
				$rowcounter++;
				$worksheet->writeString($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("label")), $format_bold);
				$worksheet->writeString($rowcounter, 1, ilExcelUtils::_convert_text($this->label));
				break;
		}
		$rowcounter++;
		$worksheet->writeString($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("question")), $format_bold);
		$worksheet->writeString($rowcounter, 1, ilExcelUtils::_convert_text($this->getQuestiontext()));
		$rowcounter++;
		$worksheet->writeString($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("question_type")), $format_bold);
		$worksheet->writeString($rowcounter, 1, ilExcelUtils::_convert_text($this->lng->txt($this->getQuestionType())));
		$rowcounter++;
		$worksheet->writeString($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("users_answered")), $format_bold);
		$worksheet->write($rowcounter, 1, $eval_data["USERS_ANSWERED"]);
		$rowcounter++;
		$worksheet->writeString($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("users_skipped")), $format_bold);
		$worksheet->write($rowcounter, 1, $eval_data["USERS_SKIPPED"]);
		$rowcounter++;

		$worksheet->write($rowcounter, 0, $this->lng->txt("subtype"), $format_bold);
		switch ($this->getSubtype())
		{
			case SUBTYPE_NON_RATIO:
				$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($this->lng->txt("non_ratio")), $format_bold);
				break;
			case SUBTYPE_RATIO_NON_ABSOLUTE:
				$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($this->lng->txt("ratio_non_absolute")), $format_bold);
				break;
			case SUBTYPE_RATIO_ABSOLUTE:
				$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($this->lng->txt("ratio_absolute")), $format_bold);
				break;
		}
		$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("mode")), $format_bold);
		$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($eval_data["MODE"]));
		$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("mode_text")), $format_bold);
		$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($eval_data["MODE"]));
		$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("mode_nr_of_selections")), $format_bold);
		$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($eval_data["MODE_NR_OF_SELECTIONS"]));
		$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("median")), $format_bold);
		$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($eval_data["MEDIAN"]));
		$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("arithmetic_mean")), $format_bold);
		$worksheet->write($rowcounter++, 1, ilExcelUtils::_convert_text($eval_data["ARITHMETIC_MEAN"]));
		$worksheet->write($rowcounter, 0, ilExcelUtils::_convert_text($this->lng->txt("values")), $format_bold);
		$worksheet->write($rowcounter, 1, ilExcelUtils::_convert_text($this->lng->txt("value")), $format_title);
		$worksheet->write($rowcounter, 2, ilExcelUtils::_convert_text($this->lng->txt("category_nr_selected")), $format_title);
		$worksheet->write($rowcounter++, 3, ilExcelUtils::_convert_text($this->lng->txt("percentage_of_selections")), $format_title);
		$values = "";
		if (is_array($eval_data["values"]))
		{
			foreach ($eval_data["values"] as $key => $value)
			{
				$worksheet->write($rowcounter, 1, ilExcelUtils::_convert_text($value["value"]));
				$worksheet->write($rowcounter, 2, ilExcelUtils::_convert_text($value["selected"]));
				$worksheet->write($rowcounter++, 3, ilExcelUtils::_convert_text($value["percentage"]), $format_percent);
			}
		}
	}

	/**
	* Adds the values for the user specific results export for a given user
	*
	* @param array $a_array An array which is used to append the values
	* @param array $resultset The evaluation data for a given user
	* @access public
	*/
	function addUserSpecificResultsData(&$a_array, &$resultset)
	{
		if (count($resultset["answers"][$this->getId()]))
		{
			foreach ($resultset["answers"][$this->getId()] as $key => $answer)
			{
				array_push($a_array, $answer["value"]);
			}
		}
		else
		{
			array_push($a_array, $this->lng->txt("skipped"));
		}
	}
	
	/**
	* Returns an array containing all answers to this question in a given survey
	*
	* @param integer $survey_id The database ID of the survey
	* @return array An array containing the answers to the question. The keys are either the user id or the anonymous id
	* @access public
	*/
	function &getUserAnswers($survey_id)
	{
		global $ilDB;
		
		$answers = array();

		$result = $ilDB->queryF("SELECT svy_answer.* FROM svy_answer, svy_finished WHERE svy_finished.survey_fi = %s AND svy_answer.question_fi = %s AND svy_finished.finished_id = svy_answer.active_fi",
			array('integer','integer'),
			array($survey_id, $this->getId())
		);
		while ($row = $ilDB->fetchAssoc($result))
		{
			$answers[$row["active_fi"]] = $row["value"];
		}
		return $answers;
	}

	/**
	* Import response data from the question import file
	*
	* @return array $a_data Array containing the response data
	* @access public
	*/
	function importResponses($a_data)
	{
		foreach ($a_data as $id => $data)
		{
			$this->setMinimum($data["min"]);
			$this->setMaximum($data["max"]);
		}
	}

	/**
	* Returns if the question is usable for preconditions
	*
	* @return boolean TRUE if the question is usable for a precondition, FALSE otherwise
	* @access public
	*/
	function usableForPrecondition()
	{
		return TRUE;
	}

	/**
	* Returns the available relations for the question
	*
	* @return array An array containing the available relations
	* @access public
	*/
	function getAvailableRelations()
	{
		return array("<", "<=", "=", "<>", ">=", ">");
	}

	/**
	* Creates a value selection for preconditions
	*
	* @param object $template The template for the value selection (usually tpl.svy_svy_add_constraint.html)
	* @access public
	*/
	function outPreconditionSelectValue(&$template)
	{
		$template->setCurrentBlock("textfield");
		$template->setVariable("TEXTFIELD_VALUE", "");
		$template->parseCurrentBlock();
	}
	
	/**
	* Creates a form property for the precondition value
	*
	* @return The ILIAS form element
	* @access public
	*/
	public function getPreconditionSelectValue($default = "", $title, $variable)
	{
		include_once "./Services/Form/classes/class.ilNumberInputGUI.php";
		$step3 = new ilNumberInputGUI($title, $variable);
		$step3->setValue($default);
		return $step3;
	}

	/**
	* Creates a text for the input range of the metric question
	*
	* @return string Range text
	* @access private
	*/
	function getMinMaxText()
	{
		$min = $this->getMinimum();
		$max = $this->getMaximum();
		if (strlen($min) && strlen($max))
		{
			return "(" . $min . " " . strtolower($this->lng->txt("to")) . " " . $max . ")";
		}
		else if (strlen($min))
		{
			return "(&gt;= " . $min . ")";
		}
		else if (strlen($max))
		{
			return "(&lt;= " . $max . ")";
		}
		else
		{
			return "";
		}
	}
}
?>