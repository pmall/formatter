<?php

namespace Pmall\Formatter;

use League\Fractal\Serializer\ArraySerializer;

class JsonSerializer extends ArraySerializer
{
    /**
     * Do not embed the data into a wrapper if resourceKey is false.
     *
     * @param string|boolean $resourceKey
     * @param array  $data
     *
     * @return array
     */
    public function collection($resourceKey, array $data)
    {
        if ($resourceKey === false) return $data;

        return [$resourceKey ?: 'data' => $data];
    }
}
