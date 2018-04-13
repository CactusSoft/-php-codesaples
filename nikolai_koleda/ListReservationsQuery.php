<?php

namespace MusicAcademy\PlannerApi\Planner\Query;
use Doctrine\ORM\EntityManagerInterface;
use MusicAcademy\PlannerApi\Application\Command\CommandInterface;
use MusicAcademy\PlannerApi\Application\Validator\ValidationRule\ValidationRuleInterface;
use MusicAcademy\PlannerApi\Planner\DTO\Transformers\ReservationTransformer;
use MusicAcademy\PlannerApi\Planner\Entity\Reservation;
use MusicAcademy\PlannerApi\Planner\Entity\Room;
use MusicAcademy\PlannerApi\Planner\Query\Validation\ListReservationsValidationRule;

/**
 * Class ListReservationsQuery
 * @package MusicAcademy\PlannerApi\Planner\Query
 */
class ListReservationsQuery implements CommandInterface
{
    const
        F_ID = 'id',

        C_FROM_DATETIME = 'from_datetime',
        C_TO_DATETIME = 'to_datetime',
        C_BUILDINGS = 'buildings',
        C_ROOMS = 'rooms',
        C_TYPES = 'types',
        C_SETS = 'sets'
    ;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * ListReservationsQuery constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @param $commandArgument
     * @return mixed
     */
    public function execute($commandArgument)
    {
        $qry = "";
        $params = [];

        $params['from_datetime'] = $commandArgument[self::C_FROM_DATETIME];
        $params['to_datetime'] = $commandArgument[self::C_TO_DATETIME];

        //Rooms
        $roomsList = null;
        if (isset($commandArgument[self::C_ROOMS])) {
            $roomsList = (array)$commandArgument[self::C_ROOMS];
        }

        //Buildings
        if (isset($commandArgument[self::C_BUILDINGS])) {
            $buildingsList = (array)$commandArgument[self::C_BUILDINGS];
            $roomsIdsFromBuildings = $this->em->getRepository(Room::class)->getAllRoomsIdForBuildingsId($buildingsList);
            if (is_null($roomsList)) {
                $roomsList = $roomsIdsFromBuildings;
            } else {
                $roomsList = array_intersect($roomsList, $roomsIdsFromBuildings);
            }
        }

        //Sets
        if (isset($commandArgument[self::C_SETS])) {
            $setsList = (array)$commandArgument[self::C_SETS];
            $roomsIdsFromSets = $this->em->getRepository(Room::class)->getAllRoomsIdForSetsId($setsList);
            if (is_null($roomsList)) {
                $roomsList = $roomsIdsFromSets;
            } else {
                $roomsList = array_intersect($roomsList, $roomsIdsFromSets);
            }
        }

        if (!is_null($roomsList)) {
            $qry .= " AND IDENTITY(r.room) IN (:rooms_list) ";
            $params['rooms_list'] = array_unique($roomsList);
        }

        //Types
        if (isset($commandArgument[self::C_TYPES])) {
            $typesList = (array)$commandArgument[self::C_TYPES];
            $qry .= " AND r.type IN (:types) ";
            $params['types'] = $typesList;
        }

        $querySQL = "
            SELECT 
                r, c
            FROM 
                " . Reservation::class . " r JOIN
                r.reservationCalendar c 
            WHERE r.status = :status 
                {$qry} AND 
                c.beginning <= :to_datetime AND 
                c.ending >= :from_datetime
        ";
        $params['status'] = Reservation::STATUS_APPOVED;
        $query = $this->em->createQuery($querySQL);
        if ($params) {
            $query->setParameters($params);
        }
        $items = $query->getResult();

        $itemsIds = [];
        foreach ($items as $v) {
            $itemsIds[] = $v->getId();
        }

        $repository = $this->em->getRepository(Reservation::class);
        $repository->populateReservationsGeneralInfo($itemsIds);
        $repository->populateReservationsAgendaInfo($itemsIds);

        /**
         * @var $rA Reservation
         */
        foreach ($items as $k => $rA) {
            $items[$k] = ReservationTransformer::transform($rA);
        }
        return $items;
    }

    /**
     * @return ValidationRuleInterface
     */
    public function getValidation() : ValidationRuleInterface
    {
        return new ListReservationsValidationRule();
    }

}