<?php
/**
 * Created by PhpStorm.
 * User: Max
 * Date: 2017-11-22
 * Time: 1:04 PM
 */

require_once '../include.php';

(function () {


	$mgr = new UserMgr();

	$currUser = $GLOBALS['user'];
	/* @var $currUser User */

	$otherUser = null;
	$filterHTML = '';
	if ($currUser->getType() == Constants::LVL_SUPERVISOR) {
		$users = $mgr->listUsers();

		$filterOptions = "";
		foreach ($users as $user) {
			if ($user->getType() == Constants::LVL_STUDENT) {
				if (isset($_GET['other']) and $_GET['other'] == $user->getID()) {
					$filterOptions .= <<<HTML
                <option value='{$user->getID()}' selected>{$user->getName()}</option>
HTML;
				} else {
					$filterOptions .= <<<HTML
                <option value='{$user->getID()}'>{$user->getName()}</option>
HTML;
				}
			}
		}

		$filterHTML = <<<HTML
        <form action="shifts.php">
            <div class="input-row">
                <label>Filter:</label>
                <select name="other">
                    <option value="">all</option>
                    {$filterOptions}
                </select>
                <input type="submit" value="Go">
            </div>
        </form>
HTML;
	}


	if (isset($_GET['other']) and $_GET['other'] !== '') {
		$otherUser = new User($_GET['other']);
	}

	$shifts = $currUser->listShifts($otherUser);

	$shiftsHTML = "";

	function comp(Shift $a, Shift $b)
	{
		if ($a->getStatus() == $b->getStatus()) {
			if ($a->getDateCompleted() == $b->getDateCompleted()) {
				return 0;
			} else if ($a->getDateCompleted() < $b->getDateCompleted()) {
				return 1;
			} else {
				return -1;
			}
		} else if ($a->getStatus() < $b->getStatus()) {
			return 1;
		} else {
			return -1;
		}
	}

	usort($shifts, 'comp');

	foreach ($shifts as $shift/* @var $shift Shift */) {
		if ($shift->getSupervisor() === null) {
			$supervisor = "unspecified";
		} else {
			$supervisor = $shift->getSupervisor()->getName();
		}

		$shiftsHTML .= <<<HTML
        <tr>
        	<td>{$shift->getID()}</td>
            <td>{$shift->getStudent()->getName()}</td>
            <td>{$supervisor}</td>
            <td>{$shift->getActivity()}</td>
            <td>{$shift->getDuration()}</td>
            <td>{$shift->getDateCompleted()->format('Y-m-d')}</td>
HTML;

		if ($shift->getStatus() == Constants::STATUS_UNSIGNED) {
			$shiftsHTML .= <<<HTML
            	<td>unsigned</td>
HTML;
		} else {
			$shiftsHTML .= <<<HTML
            	<td>signed</td>
HTML;
		}

		if ($currUser->getType() == Constants::LVL_SUPERVISOR) {
			if ($shift->getStatus() == Constants::STATUS_UNSIGNED) {
				$shiftsHTML .= <<<HTML
            <td><a href="javascript: setPage(['id', '{$shift->getID()}'], 'handlers/signShift.php');">sign</a></td>
HTML;

			} else {
				$shiftsHTML .= <<<HTML
            <td><a href="javascript: setPage(['id', '{$shift->getID()}'], 'handlers/signShift.php');">unsign</a></td>
HTML;
			}
		}

		$shiftsHTML .= "</tr>";
	}

	$page = <<<HTML
	<h1 id="title">Shifts</h1>
    <div id="actions">
    {$filterHTML}
    <button onclick="setPage([], 'add_shift.php')">Create</button>
    </div>
    <table border="1">
        <tr>
        	<th>ID</th>
            <th>Student</th>
            <th>Supervisor</th>
            <th>Activity</th>
            <th>Duration</th>
            <th>Date</th>
            <th>Status</th>
HTML;

	if ($currUser->getType() == Constants::LVL_SUPERVISOR)
		$page.=<<<HTML
		<th>Action</th>
HTML;


	$page.=<<<HTML
        </tr>
        {$shiftsHTML}
    </table>
HTML;
	echo PageWrapper::render([
		'title' => 'List of Shifts',
		'content' => $page
	]);
})();
