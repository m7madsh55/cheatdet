<?php

/**
 * @package     quiz_cheatdet
 * @copyright   2021 mohammad shatarah
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot."/mod/quiz/report/cheatdet/classes/quiz_cheatdet_fraction.php");

require_once($CFG->dirroot."/mod/quiz/report/cheatdet/locallib.php");
/**
 * The class report provides a dynamic spreadsheet of the quiz.
 *
 * It gives the most recent answers from all students. It does not do grading.
 * There is an option to show what the grades would be if the quiz were graded at that moment.
 */

class quiz_cheatdet_report extends quiz_default_report {

	 /** @var context_module context of this quiz.*/
    protected $context;
    protected $group = 0;
    /** @var int The time of the last student response to a question. */
    protected $qmaxtime = 0;
    /** @var int The course module id for the quiz. */
    protected $id = 0;
    /** @var String The string that tells the code in quiz/report which sub-module to use. */
    protected $mode = '';
    /** @var int The context id for the quiz. */
    protected $quizcontextid = 0;
    /** @var Array The sorted array of the students who are attempting the quiz. */
    protected $users = array();
    /** @var Array The array of the students who have attempted the quiz. */
    protected $sofar = array();

    public function display($quiz, $cm, $course) {
        
        global $OUTPUT, $DB, $CFG, $USER;

        $slots = array();
        $question = array();
        $users = array();
        $sofar = array();
        $quizid = $quiz->id;
        $answer = '';
        $stanswers = array();
        $stfraction = array();
        

        $this->context = context_module::instance($cm->id);
        require_capability('mod/quiz:viewreports', $this->context);
        $context = $DB->get_record('context', array('instanceid' => $cm->id, 'contextlevel' => 70));
        $quizcontextid = $context->id;
    	

    	$sofar = cheatwho_sofar_gridview($quizid);

    	$initials = array();
        if (count($sofar) > 0) {
            foreach ($sofar as $unuser) {
                // If only a group is desired, make sure this student is in the group.
                if ($group) {
                    if ($DB->get_record('groups_members', array('groupid' => $group, 'userid' => $unuser))) {
                        $getresponse = true;
                    } else {
                        $getresponse = false;
                    }
                } else {
                    $getresponse = true;
                }
                if ($getresponse) {
                    $usr = $DB->get_record('user', array('id' => $unuser));
                    if ($order) {
                        $initials[$unuser] = $usr->firstname.'&nbsp;'.$usr->lastname .',&nbsp;' .$usr->id;
                    } else {
                        $initials[$unuser] = $usr->lastname.',&nbsp;'.$usr->firstname .',&nbsp;' .$usr->id;
                    }
                }
            }
        }
        		// These arrays are the 'answr' or 'fraction' indexed by userid and questionid.
        
        $stanswers = array();
        $stfraction = array();
        list($stanswers, $stfraction) = cheatDet_get_answers($quizid);
       
         	/*echo "<h1>$users[0]</h1>
         		<h2>$name</h2>					"
        

       			;*/
       			echo $OUTPUT->header();
       	
       		foreach($initials as $result) {
   				  echo $result, '<br>';
								 }
				

        echo $OUTPUT->footer();
    }
}


