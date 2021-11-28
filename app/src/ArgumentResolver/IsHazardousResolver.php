<?php

namespace App\ArgumentResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class IsHazardousResolver implements ArgumentValueResolverInterface
{
    private const ALLOWED_ROUTES = [
        'neo_fastest',
        'neo_best_month',
    ];

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        $isAllowedType = is_null($argument->getType()) || is_string($argument->getType());
        $isAllowedRoute = in_array($request->attributes->get('_route'), self::ALLOWED_ROUTES) === true;

        if ($isAllowedType && $isAllowedRoute) {
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
            $errorMessage = "Parameter 'hazardous' should be equal 'true' or 'false' or be completely absent";
            throw new BadRequestHttpException($errorMessage);
        }
    }
}
