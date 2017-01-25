<?

namespace {{ $namespace }};

use League\Fractal;
use {{ $modelName }};

class {{ $class }} extends Fractal\TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
@foreach ($relationships as $relationship)
        '{{ $relationship['method'] }}',
@endforeach
    ];

    /**
     * Transform {{ $name }}
     *
     * {{ '@param' }}   {{ $name }} ${{ camel_case($name) }}
     * {{ '@return' }}  array
     */
    public function transform({{ $name }} ${{ camel_case($name) }})
    {
        return [
@foreach ($columnTypes as $columnType)
            '{{ $columnType['name'] }}' =>@if ($columnType['cast'] === true) ({{ $columnType['type'] }})@endif ${{ camel_case($name) }}->{{ $columnType['name'] }},
@endforeach
        ];
    }

@foreach ($relationships as $relationship)
    /**
     * Include {{ ucfirst($relationship['method']) }}
     *
     * {{ '@param' }}   {{ $name }} ${{ camel_case($name) }}
     * {{ '@return' }}  \League\Fractal\Resource\{{ ucfirst($relationship['relationCountType']) }}
     */
    public function include{{ ucfirst($relationship['method']) }}({{ $name }} ${{ camel_case($name) }})
    {
        return $this->{{ $relationship['relationCountType'] }}(${{ camel_case($name) }}->{{ $relationship['method'] }}, new {{ $relationship['relatedClass']}}Transformer);
    }

@endforeach
}
