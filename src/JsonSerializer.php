<?php

namespace NilPortugues\Api\Json;

use NilPortugues\Api\Mapping\Mapper;
use NilPortugues\Serializer\DeepCopySerializer;

class JsonSerializer extends DeepCopySerializer
{
    /**
     * JsonSerializer constructor.
     *
     * @param Mapper $mapper
     */
    public function __construct(Mapper $mapper)
    {
        parent::__construct(new JsonTransformer($mapper));
    }

    /**
     * @return JsonTransformer
     */
    public function getTransformer()
    {
        return $this->serializationStrategy;
    }
    /**
     * @param mixed $value
     *
     * @return string
     */
    public function serialize($value)
    {
        return parent::serialize($value);
    }
}
