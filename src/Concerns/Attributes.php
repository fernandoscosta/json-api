<?php

declare(strict_types=1);

namespace TiMacDonald\JsonApi\Concerns;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\PotentiallyMissing;
use Illuminate\Support\Collection;
use TiMacDonald\JsonApi\Support\Fields;

/**
 * @internal
 */
trait Attributes
{
    private static bool $minimalAttributes = false;

    /**
     * @return void
     */
    public static function minimalAttributes($value = true)
    {
        self::$minimalAttributes = $value;
    }

    /**
     * @return Collection<string, mixed>
     */
    private function requestedAttributes(Request $request)
    {
        return Collection::make($this->resolveAttributes($request))
            ->only($this->requestedFields($request))
            ->map(fn (mixed $value): mixed => value($value))
            ->reject(fn (mixed $value): bool => $value instanceof PotentiallyMissing && $value->isMissing());
    }

    /**
     * @return Collection<string, mixed>
     */
    private function resolveAttributes(Request $request)
    {
        return Collection::make(property_exists($this, 'attributes') ? $this->attributes : [])
            ->mapWithKeys(fn (string $attribute, int|string $key): array => [
                $attribute => fn () => $this->resource->{$attribute},
            ])
            ->merge($this->toAttributes($request));
    }

    /**
     * @return array<int, string>|null
     */
    private function requestedFields(Request $request)
    {
        return Fields::getInstance()->parse($request, $this->toType($request), self::$minimalAttributes);
    }
}
