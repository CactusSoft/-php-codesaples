<?php

namespace MusicAcademy\PlannerApiBundle\Service\Application;

use MusicAcademy\PlannerApi\Components\ICS\ICSGenerator;
use MusicAcademy\PlannerApi\Components\Logger;
use MusicAcademy\PlannerApi\Planner\Entity\Reservation;
use MusicAcademy\PlannerApi\Planner\Entity\ReservationFeature\Preparation;
use MusicAcademy\PlannerApi\Security\Service\SecurityContextInterface;

/**
 * Class Emails
 * @package MusicAcademy\PlannerApiBundle\Service\Application
 * Main e-mail sender
 */
class Emails
{
    const
        TEMPLATE_NEW_REQUEST_TO_ADMIN = 'new_request_to_admin',
        TEMPLATE_NEW_DIRECT_RESERVATION = 'new_direct_reservation',
        TEMPLATE_YOUR_REQUEST_IS_APPROVED = 'your_request_is_approved',
        TEMPLATE_YOUR_REQUEST_IS_CANCELLED = 'your_request_is_cancelled',
        TEMPLATE_YOUR_REQUEST_IS_EDITED = 'your_request_is_edited',
        TEMPLATE_YOUR_REQUEST_IS_DELETED = 'your_request_is_deleted',
        TEMPLATE_JANITOR_EMAIL = 'janitor_email'
    ;

    /**
     * Project root path
     * @var String
     */
    protected $rootPath;
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;
    /**
     * Admin's e-mail
     * @var String
     */
    protected $admin_email;
    /**
     * @var \Twig_Environment
     */
    protected $twig;
    /*
     * Url to front
     * @var String
     */
    protected $front_uri;
    /**
     * @var SecurityContextInterface
     */
    protected $security_context;
    /**
     * @var ICSGenerator
     */
    protected $ics_generator;
    /**
     * @var Logger
     */
    protected $logger;


    /**
     * Emails constructor.
     * @param \Swift_Mailer $mailer
     * @param \Twig_Environment $twig
     * @param SecurityContextInterface $security_context
     * @param String $rootPath
     * @param String $admin_email
     * @param String $front_uri
     * @param ICSGenerator $ics_generator
     * @param Logger $logger
     */
    function __construct(
        \Swift_Mailer $mailer,
        \Twig_Environment $twig,
        SecurityContextInterface $security_context,
        String $rootPath,
        String $admin_email,
        String $front_uri,
        ICSGenerator $ics_generator,
        Logger $logger
    )
    {
        $this->rootPath = $rootPath;
        $this->mailer = $mailer;
        $this->admin_email = $admin_email;
        $this->twig = $twig;
        $this->front_uri = $front_uri;
        $this->security_context = $security_context;
        $this->ics_generator = $ics_generator;
        $this->$logger = $logger;
    }

    /**
     * Prepare variables to replacement
     * @param Reservation $reservation
     * @return array
     */
    private function prepareVariables(Reservation $reservation)
    {
        $replacements = [
            "UserName" => $this->security_context->getCurrentUser()->getTitle(),
            "EventName" => $reservation->getName(),
            "JanitorMessage" => $reservation->getJanitorMsg(),
            "RoomName" => $reservation->getRoom()->getName(),
            "BuildingName" => $reservation->getRoom()->getBuilding()->getName(),
            "DateTime" => date("d.m.Y H:i"),
            "IncomingRequestsLink" => 'http://' . $this->front_uri . '/requests/incoming',
            "ReservationCreateDate" => $reservation->createdAt()->format("d.m.Y"),
            "ReservationCreateDateTime" => $reservation->createdAt()->format("d.m.Y H:i"),
            "OutcomingRequestsLink" => 'http://' . $this->front_uri . '/requests/outcoming',
            "DashboardLink" => 'http://' . $this->front_uri . '/dashboard',
            "RemovalLink" => 'http://' . $this->front_uri . '/remove?reservation_id=' . $reservation->getId()
        ];
        $replacements['Preparations'] = '';
        foreach ($reservation->getPreparations() as $onePreparaion) {
            /**
             * @var $onePreparaion Preparation
             */
            if ($onePreparaion->getPosition() == Preparation::POSITION_BEFORE) {
                $timeTo = clone $reservation->getBeginning();
                $timeTo =  $timeTo->modify('+' . $onePreparaion->getDuration() . ' minutes');

                $timeFrom = clone $reservation->getBeginning();
            } else {
                $timeTo =  clone $reservation->getEnding();
                $timeFrom = clone $reservation->getEnding();
                $timeFrom =  $timeFrom->modify('-' . $onePreparaion->getDuration() . ' minutes');
            }
            $replacements['Preparations'] .= "\n" . ' - '. $timeFrom->format('d.m.Y') . ', ' . $timeFrom->format('H:i') . ' - ' . $timeTo->format('H:i') . ', ' .
                Preparation::getTranslatedTitle($onePreparaion->getType());

        }
        return $replacements;
    }

    /**
     * Sends email with choosen template
     * @param String|array $emails
     * @param String $template
     * @param Reservation $reservation
     * @param String $from
     *
     * @return bool
     */
    public function send($emails, String $template, Reservation $reservation, $from = '')
    {
        $emails = (array)$emails;
        if (!$from) {
            $from = $this->admin_email;
        }
        $replacements = $this->prepareVariables($reservation);

        $text = $this->twig->render(
            'email_templates/' . $template . '.html.twig',
            $replacements
        );
        $data = explode("\n", $text, 2); //subject in first line of template
        try {
            foreach ($emails as $email) {
                $message = \Swift_Message::newInstance()
                    ->setSubject(trim($data[0]))
                    ->setFrom($from)
                    ->setTo($email)
                    ->setBody(trim(nl2br($data[1]) ?? ''), 'text/html');
                /**
                 * @var $message \Swift_Message
                 */
                if (in_array($template, [
                    self::TEMPLATE_NEW_DIRECT_RESERVATION,
                    self::TEMPLATE_YOUR_REQUEST_IS_EDITED,
                    self::TEMPLATE_YOUR_REQUEST_IS_APPROVED,
                    self::TEMPLATE_JANITOR_EMAIL
                ])) {
                    $fname = $reservation->getId() . '.ics';
                    $message->attach(\Swift_Attachment::newInstance($this->ics_generator->getFromReservation($reservation), $fname, 'text/calendar'));
                }
                $this->mailer->send($message);
            }
        } catch (\Exception $e) {
            $this->logger->alert("E-mail wasn't sent\n" . $e->getMessage());
            return false;
        }
        return true;
    }
}