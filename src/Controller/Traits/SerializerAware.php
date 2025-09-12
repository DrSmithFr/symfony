<?php

namespace App\Controller\Traits;

use App\Entity\Serializable;
use App\Model\Form\FormErrorDetailModel;
use App\Model\Form\FormErrorModel;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ReadableCollection;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait SerializerAware
{
    /**
     * @var SerializerInterface|null
     */
    private ?SerializerInterface $serializer;

    private function setSerializer(SerializerInterface $serializer): void
    {
        $this->serializer = $serializer;
    }

    private function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }

    /**
     * Create serialization context for specifics groups
     * with serialize null field enable
     */
    private function getSerializationContext(array $group = ['Default']): SerializationContext
    {
        $context = SerializationContext::create();
        $context->setSerializeNull(true);
        $context->setGroups($group);
        return $context;
    }

    /**
     * Return the array version of the data, serialize for specifics groups
     */
    protected function toArray(Serializable $data, array $group = ['Default']): array
    {
        $json = $this
            ->getSerializer()
            ->serialize($data, 'json', $this->getSerializationContext($group));

        return json_decode($json, true);
    }

    /**
     * Return the json string of the data, serialize for specifics groups
     *
     * @param Serializable|Collection|ReadableCollection|array $data
     * @param string[]                                         $group
     *
     * @return string
     */
    protected function serialize(
        Serializable|Collection|ReadableCollection|array $data,
        array $group = ['Default']
    ): string {
        return $this
            ->getSerializer()
            ->serialize(
                $data,
                'json',
                $this->getSerializationContext($group)
            );
    }

    /**
     * Return the JsonResponse of the data, serialize for specifics groups
     *
     * @param Serializable|Collection|ReadableCollection|array $data
     * @param String[]                                         $group
     * @param int                                              $status
     *
     * @return JsonResponse
     */
    protected function serializeResponse(
        Serializable|Collection|ReadableCollection|array $data,
        array $group = ['Default'],
        int $status = Response::HTTP_OK
    ): JsonResponse {
        $response = new JsonResponse([], $status);
        $json = $this->serialize($data, $group);
        return $response->setJson($json);
    }

    /**
     * Simple JsonResponse use to transmit a message
     */
    protected function messageResponse(string $message, int $status = Response::HTTP_OK): JsonResponse
    {
        return new JsonResponse(
            [
                'message' => $message,
            ],
            $status
        );
    }

    /**
     * Simple JsonResponse use to transmit a message
     */
    protected function formErrorResponse(
        FormInterface $form,
        int $status
    ): JsonResponse {
        $formError = (new FormErrorModel())
            ->setCode($status)
            ->setReason($this->getFormErrorDetail($form));
        return new JsonResponse(
            $this->toArray($formError),
            $status
        );
    }

    private function getFormErrorDetail(FormInterface $data): FormErrorDetailModel
    {
        $reason = new FormErrorDetailModel();
        $errors = [];

        foreach ($data->getErrors(true) as $error) {
            $path = $this->getFormPath($error->getOrigin());
            $errors[$path] = $error->getMessage();
        }

        $reason->setErrors($errors);

        return $reason;
    }

    private function getFormPath(FormInterface $form): string
    {
        $parent = $form->getParent();

        if ($parent && $parent->getParent()) {
            return $this->getFormPath($parent) . '.' . $form->getName();
        }

        return $form->getName();
    }
}
