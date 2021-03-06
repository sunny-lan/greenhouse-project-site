<?php

/**
 * Created by PhpStorm.
 * User: Max
 * Date: 2017-11-20
 * Time: 2:16 PM
 */
class Box extends DBObject
{
    function __construct(int $id)
    {
        parent::__construct('boxes', $id);
    }


    //description

    function getDescription()
    {
        return $this->selectF('description');
    }

    function setDescription(string $description)
    {
        $this->updateF('description', $description);
    }


	//name

	function getName()
	{
		return $this->selectF('name');
	}

	function setName(string $description)
	{
		$this->updateF('name', $description);
	}

    // dimensions

    function getRect()
    {
        return [
            'top' => $this->selectF('top'),
            'l' => $this->selectF('l'),
            'bottom' => $this->selectF('bottom'),
            'r' => $this->selectF('r')
        ];
    }

    function setRect(array $param)
    {
        $this->updateF('top', $param['top']);
        $this->updateF('l', $param['l']);
        $this->updateF('bottom', $param['bottom']);
        $this->updateF('r', $param['r']);
    }


    //plants

    function addPlantEntry(array $param)
    {
        $plant = $param['plant'];
        $startDate = $param['startDate'];

        $mgr = new BoxPlantEntryMgr();
        return $mgr->createBoxPlantEntry($this, $plant, $startDate);
    }

    function listPlantEntries(DateTime $startDate = null, DateTime $endDate = null)
    {
        $myID = $this->getID();
        $query = "SELECT id FROM box_plants WHERE box_id = '$myID'";


        if (isset($endDate)) {
            $endDate = Util::datePHP2SQL($endDate);
            $query .= " AND start_date <= '$endDate'";
        }

        if (isset($startDate)) {
            $startDate = Util::datePHP2SQL($startDate);
            $query .= " AND COALESCE(end_date, 9999-12-31) >= '$startDate'";
        }
        $sqlRes = Util::queryW($this->db, $query);

        $res = [];
        while ($row = $sqlRes->fetch_assoc())
            $res[] = new BoxPlantEntry($row['id']);
        return $res;
    }


    //list harvests

    function listHarvests(DateTime $startDate = null, DateTime $endDate = null)
    {
        $myID = $this->getID();
        $query = "SELECT id FROM harvest WHERE box_id = '$myID'";


        if (isset($endDate)) {
            $endDate = Util::datePHP2SQL($endDate);
            $query .= " AND date_harvested <= '$endDate'";
        }

        if (isset($startDate)) {
            $startDate = Util::datePHP2SQL($startDate);
            $query .= " AND date_harvested >= '$startDate'";
        }
        $sqlRes = Util::queryW($this->db, $query);

        $res = [];
        while ($row = $sqlRes->fetch_assoc())
            $res[] = new Harvest($row['id']);
        return $res;
    }

    //delete

    function delete()
    {
        $id = $this->id;
        Util::queryW($this->db, "DELETE FROM boxes WHERE id='$id'");
        $this->id = -1;
    }
}