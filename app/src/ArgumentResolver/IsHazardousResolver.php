<?php

namespace App\ArgumentResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class IsHazardousResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        if (is_null($argument->getType()) || is_string($argument->getType())) {
            return true;
        }

        return false;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $isHazardous = $request->get('hazardous');

        if ($isHazardous === 'true') {
            yield true;
        } elseif ($isHazardous === 'false' || empty($isHazardous)) {
            yield false;
        } else {
            $errorMessage = "Parameter 'hazardous' be equal 'true' or 'false' or be completely absent";
            throw new BadRequestHttpException($errorMessage);
        }
    }
}
