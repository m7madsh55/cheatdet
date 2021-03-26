<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin internal classes, functions and constants are defined here.
 *
 * @package     quiz_cheatdet
 * @copyright   2021 mohammad shatarah
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Handle the mod_quiz\event\attempt_started event.
 *
 * @param object $event The event object.
 */
function observe_startted($event) {
	///READ EVENTS DOCS
    return;
}

/**
 * Handle the mod_quiz\event\attempt_submitted event.
 *
 * @param object $event The event object.
 */
function observe_submitted($event) {

	///READ EVENTS DOCS 
    return;
}

/**
 * Return the number of users who have submitted answers to this quiz instance.
 *
 * @param int $quizid The ID for the quiz instance.
 * @return array The userids for all the students submitting answers.
 */
function cheatwho_sofar_gridview($quizid) {
    global $DB;

    $records = $DB->get_records('quiz_attempts', array('quiz' => $quizid));
    $studentrole = $DB->get_record('role', array('shortname' => 'student'));
    $quiz = $DB->get_record('quiz', array('id' => $quizid));
    $context = context_course::instance($quiz->course);
    foreach ($records as $record) {
        if ($DB->get_record('role_assignments', array('contextid' => $context->id,
            'roleid' => $studentrole->id, 'userid' => $record->userid))) {
            $userid[] = $record->userid;
        }
    }
    if (isset($userid)) {
        return(array_unique($userid));
    } else {
        return(null);
    }
}

/**
 * Return the first and last name of a student.
 *
 * @param int $userid The ID for the student.
 * @return string The last name, first name of the student.
 */
function cheatDet_find_student($userid) {
     global $DB;
     $user = $DB->get_record('user', array('id' => $userid));
     $name = $user->firstname." ".$user->lastname;
     return($name);
}

/**
 * Return the first and last name of a student.
 *
 * @param int $userid The ID for the student.
 * @return string The last name, first name of the student.
 */
function cheatDet_student($userid) {
     global $DB;
     $user = $DB->get_record('user', array('id' => $userid));
     $name = $user->firstname." ".$user->lastname;
     return($name);
}
/**
 * A function to return the most recent response of all students to the questions in a quiz and the grade for the answers.
 *
 * @param int $quizid The id for the quiz.
 * @return array $returnvalues. $returnvalues[0] = $stanswers[$stid][$qid], $returnvalues[1] = $stfraction[$stid][$qid].
 **/
function cheatDet_get_answers($quizid) {
    global $DB;
    $quizattempts = $DB->get_records('quiz_attempts', array('quiz' => $quizid));
    // These arrays are the 'answr' or 'fraction' indexed by userid and questionid.
    $stanswers = array();
    $stfraction = array();
    foreach ($quizattempts as $key => $quizattempt) {
        $usrid = $quizattempt->userid;
        $qubaid = $quizattempt->uniqueid;
        $mydm = new quiz_cheatdet_fraction($qubaid);
        $qattempts = $DB->get_records('question_attempts', array('questionusageid' => $qubaid));
        foreach ($qattempts as $qattempt) {
            $myresponse = array();
            $qattemptsteps = $DB->get_records('question_attempt_steps', array('questionattemptid' => $qattempt->id));
            foreach ($qattemptsteps as $qattemptstep) {
                if (($qattemptstep->state == 'complete') || ($qattemptstep->state == 'invalid')
                    || ($qattemptstep->state == 'todo')) {
                    // Handling Cloze questions, 'invalid' and immediatefeedback, 'todo'.
                    $answers = $DB->get_records('question_attempt_step_data', array('attemptstepid' => $qattemptstep->id));
                    foreach ($answers as $answer) {
                        $myresponse[$answer->name] = $answer->value;
                    }
                    if (count($myresponse) > 0) {
                        $clozeresponse = array();// An array for the Close responses.
                        foreach ($myresponse as $key => $respon) {
                            // For cloze questions the key will be sub(\d*)_answer.
                            // I need to take the answer that follows part (\d):(*)?;.
                            if (preg_match('/sub(\d)*\_answer/', $key, $matches)) {
                                $clozequestionid = $qattempt->questionid;
                                // Finding the number of parts.
                                $numclozeparts = $DB->count_records('question', array('parent' => $clozequestionid));
                                $myres = array();
                                $myres[$key] = $respon;
                                $newres = $mydm->get_fraction($qattempt->slot, $myres);
                                $onemore = $numclozeparts + 1;
                                $tempans = $newres[0]."; part $onemore";
                                $index = $matches[1];
                                $nextindex = $index + 1;
                                $tempcorrect = 'part '.$matches[1].': ';
                                if (preg_match("/$tempcorrect(.*); part $nextindex/", $tempans, $ansmatch)) {
                                    $clozeresponse[$matches[1]] = $ansmatch[1];
                                }
                            }
                        }
                        $response = $mydm->get_fraction($qattempt->slot, $myresponse);
                        if (count($clozeresponse) > 0) {
                            $stanswers[$usrid][$qattempt->questionid] = $clozeresponse;
                        } else {
                            $stanswers[$usrid][$qattempt->questionid] = $response[0];
                        }
                        $stfraction[$usrid][$qattempt->questionid] = $response[1];
                    }
                }
            }
        }
    }
    $returnvalues = array($stanswers, $stfraction);
    return $returnvalues;

}