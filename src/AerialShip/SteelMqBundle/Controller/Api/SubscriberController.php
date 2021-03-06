<?php

namespace AerialShip\SteelMqBundle\Controller\Api;

use AerialShip\SteelMqBundle\Entity\Project;
use AerialShip\SteelMqBundle\Entity\Queue;
use AerialShip\SteelMqBundle\Entity\Subscriber;
use AerialShip\SteelMqBundle\Helper\SecurityHelper;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/projects/{projectId}/queues/{queueId}/subscribers")
 */
class SubscriberController extends AbstractApiController
{
    /**
     * @Route("{slash}")
     * @Method({"GET"})
     * @ParamConverter("project", options={"id" = "projectId"})
     * @ParamConverter("queue", options={"id" = "queueId"})
     * @SecureParam(name="project", permissions="PROJECT_ROLE_SUBSCRIBE")
     */
    public function listAction(Project $project, Queue $queue)
    {
        SecurityHelper::checkQueueIsInProject($project, $queue);

        $subscribers = $this->get('aerial_ship_steel_mq.manager.subscriber')->getList($queue);

        return $this->handleData($subscribers, ['Default']);
    }

    /**
     * @Route("{slash}")
     * @Method({"POST"})
     * @ParamConverter("project", options={"id" = "projectId"})
     * @ParamConverter("queue", options={"id" = "queueId"})
     * @SecureParam(name="project", permissions="PROJECT_ROLE_SUBSCRIBE")
     */
    public function createAction(Project $project, Queue $queue, Request $request)
    {
        SecurityHelper::checkQueueIsInProject($project, $queue);

        $form = $this->createForm('subscriber');

        $form->handleRequest($request);

        if (false == $form->isValid()) {
            return $this->handleView($this->view($form, 400));
        }
        /** @var Subscriber $subscriber */
        $subscriber = $form->getData();
        $this->get('aerial_ship_steel_mq.manager.subscriber')->create($queue, $subscriber);

        return $this->handleData(
            [
                'id' => $subscriber->getId(),
                'success' => true,
            ]
        );
    }

    /**
     * @Route("/{subscriberId}{slash}")
     * @Method({"DELETE"})
     * @ParamConverter("project", options={"id" = "projectId"})
     * @ParamConverter("queue", options={"id" = "queueId"})
     * @ParamConverter("subscriber", options={"id" = "subscriberId"})
     * @SecureParam(name="project", permissions="PROJECT_ROLE_SUBSCRIBE")
     */
    public function deleteAction(Project $project, Queue $queue, Subscriber $subscriber)
    {
        SecurityHelper::checkQueueIsInProject($project, $queue);
        SecurityHelper::checkSubscriberIsInQueue($queue, $subscriber);

        $this->get('aerial_ship_steel_mq.manager.subscriber')->delete($subscriber);

        return $this->handleData(["success" => true]);
    }
}
