<?php

/**
 * Set return type of get_object_taxonomies().
 */

declare(strict_types=1);

namespace SzepeViktor\PHPStan\WordPress;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\Type;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Constant\ConstantStringType;

class GetObjectTaxonomiesDynamicFunctionReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{
    public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        return in_array($functionReflection->getName(), ['get_object_taxonomies'], true);
    }

    /**
     * @see https://developer.wordpress.org/reference/functions/get_object_taxonomies/
     *
     * phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
    {
        // Called without second $output argument
        if (count($functionCall->args) <= 1) {
            return new ArrayType(new IntegerType(), new StringType());
        }

        $argumentType = $scope->getType($functionCall->args[1]->value);

        // When called with a non-string $output, return default return type
        if (! $argumentType instanceof ConstantStringType) {
            return new ArrayType(new IntegerType(), new StringType());
        }

        // Called with a string $output
        switch ($argumentType->getValue()) {
            case 'objects':
                return new ArrayType(new StringType(), new ObjectType('WP_Taxonomy'));
            case 'names':
            default:
                return new ArrayType(new IntegerType(), new StringType());
        }
    }
}
