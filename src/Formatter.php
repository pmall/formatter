<?php

namespace Pmall\Formatter;

use Illuminate\Pagination\AbstractPaginator;

use League\Fractal\Manager;
use League\Fractal\Serializer\SerializerAbstract;
use League\Fractal\TransformerAbstract;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

use JsonSerializable;
use Traversable;
use InvalidArgumentException;

class Formatter implements JsonSerializable
{
    private $formatted;

    public function __construct($data, $transformer_class, $serializer_class = null)
    {
        $transformer = $this->getTransformerInstance($transformer_class);
        $serializer = $this->getSerializerInstance($serializer_class);

        if (! $transformer) {

            $message = 'Either a transformer instance or a transformer class is expected as second parameter.';

            throw new InvalidArgumentException($message);

        }

        if (! $serializer) {

            $message = 'Either a serializer instance or a serializer class is expected as third parameter.';

            throw new InvalidArgumentException($message);

        }

        $this->formatted = $this->getFormattedData($data, $transformer, $serializer);
    }

    public function toArray()
    {
        return $this->formatted->toArray();
    }

    public function toJson()
    {
        return $this->formatted->toJson();
    }

    public function jsonSerialize()
    {
        return $this->toJson();
    }

    public function __toString()
    {
        return $this->toJson();
    }

    private function getTransformerInstance($something)
    {
        $transformer = (is_string($something)) ? resolve($something) : $something;

        if ($transformer instanceof TransformerAbstract) {

            return $transformer;

        }
    }

    private function getSerializerInstance($something)
    {
        if (! $something) return new JsonSerializer;

        $serializer = (is_string($something)) ? resolve($something) : $something;

        if ($serializer instanceof SerializerAbstract) {

            return $serializer;

        }
    }

    private function getFormattedData($data, $transformer, $serializer)
    {
        $fractal = new Manager();

        $fractal->setSerializer($serializer);

        if ($data instanceof AbstractPaginator) {

            $resource = new Collection($data->getCollection(), $transformer);

            $resource->setPaginator(new IlluminatePaginatorAdapter($data));

        } elseif (is_array($data) or $data instanceof Traversable) {

            $resource = new Collection($data, $transformer);

        } else {

            $resource = new Item($data, $transformer);

        }

        return $fractal->createData($resource);
    }
}
