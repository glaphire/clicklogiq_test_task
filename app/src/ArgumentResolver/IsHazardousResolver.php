<?php

namespace App\ArgumentResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class IsHazardousResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        if (is_null($argument->getType()) || is_string($argument->getType())) {
            return true;
        }

        return false;
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $isHazardous = $request->get('hazardous');

        if ($isHazardous === 'true') {
            yield true;
        } elseif ($isHazardous === 'false' || empty($isHazardous)) {
            yield false;
        } else {
            throw new BadRequestHttpException("Parameter 'hazardous' should be absent, 'true' or 'false'");
        }
    }
}
