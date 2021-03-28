<?php

/**
 * @package     quiz_cheatdet
 * @copyright   2021 mohammad shatarah
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot."/mod/quiz/report/cheatdet/classes/quiz_cheatdet_fraction.php");

require_once($CFG->dirroot."/mod/quiz/report/cheatdet/locallib.php");

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

                if (1) {
                    $usr = $DB->get_record('user', array('id' => $unuser));
                    if ($order) {
                        $initials[$unuser] = $usr->firstname.'&nbsp;'.$usr->lastname ;
                    } else {
                        $initials[$unuser] = $usr->lastname.',&nbsp;'.$usr->firstname ;
                    }
                }
            }
        }     
// These arrays are the 'answer' or 'fraction' indexed by userid and questionID.
$stanswers = array();
$stfraction = array();
list($stanswers, $stfraction) = cheatDet_get_answers($quizid);
$test = array();
$quiz_attempts = array();

foreach ($sofar as $key => $user ) {
  $quiz_attempts = $DB->get_records('quiz_attempts' , array( 'quiz' => $quizid , 'userid' => $user)); 
  foreach ($quiz_attempts as $attempt) { 
    $submissions[$user][$attempt->id] = $DB->get_record('logstore_standard_log' , array('action' =>  'submitted' , 'objectid' => $attempt->id));
        $ip[$user]= $submissions[$user][$attempt->id]->ip; 
        }
       }
$time_limit = max_Time($quizid);

$quizattempts = $DB->get_records('quiz_attempts', array('quiz' => $quizid));
         	
echo $OUTPUT->header();
       	
         // echo $time_limit . "</br>";
          //var_dump($ip);
          foreach ($ip as $key => $value) {
            echo "<h3>USER $key ip is $value </br><h3>";

          }
    /*echo "$duplicateIP </br>";*/
        //var_dump($duplicateIP);
        foreach ($sofar as $user ) {
  # code...
  $duplicateIP= has_dupes($ip,$user); 
  echo "duplicate ip: ".$duplicateIP[$user] . "  $user id of user with duplicate ip </br>";
}

        /*foreach ($sofar as $user) {
        
          $useranswer=$stanswers[$user];
          var_dump($useranswer);
          foreach ($useranswer as $key => $answer) {
            echo " user $user answer to question $q is $answer";
             echo "</br>";
          }
         
       }*/
        
    
    }
}


