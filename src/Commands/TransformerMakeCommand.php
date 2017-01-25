<?php

namespace lasselehtinen\Cybertron\Commands;

use DB;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Input\InputOption;

class TransformerMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:transformer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Fractal Transformer';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Transformer';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        if (!class_exists($this->option('model'))) {
            $this->error('Model does not exist.');
            exit;
        }

        if (parent::fire() === false) {
            return;
        }
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        // Create new model from the parameter
        $modelName = $this->option('model');
        $model = new $modelName;

        // Get column types, namespaces etc. needed for the generation of the Transformer
        $columnTypes = $this->getColumnTypes($model);
        $namespace = $this->getNamespace($name);
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);
        $name = (new \ReflectionClass($modelName))->getShortName();
        $relationships = $this->getRelationships($model);

        // Generate the stub using the Blade view
        $stub = view('cybertron::transformer', compact('columnTypes', 'namespace', 'class', 'modelName', 'name', 'relationships'))->render();

        // Replace short tag
        $stub = str_replace('<?', '<?php', $stub);

        return $stub;
    }

    /**
     * Get columns types for the models table and if type casting is needed
     * @param  mixed $model
     * @return Illuminate\Support\Collection
     */
    public function getColumnTypes($model)
    {
        // Generate new Collection for column types
        $columnTypes = new Collection;

        // Go through all the columns in the models table
        foreach (Schema::getColumnListing($model->getTable()) as $columnName) {
            // Get the column type
            $type = DB::connection()->getDoctrineColumn($model->getTable(), $columnName)->getType()->getName();

            // Determine if type casting is required
            switch ($type) {
                case 'integer':
                case 'boolean':
                    $cast = true;
                    break;
                default:
                    $cast = false;
                    break;
            }

            // Push to Collection
            $columnTypes->push([
                'name' => $columnName,
                'type' => $type,
                'cast' => $cast,
            ]);
        }

        return $columnTypes;
    }

    /**
     * Get the relationships for the given model
     * @param  mixed $model
     * @return Illuminate\Support\Collection
     */
    public function getRelationships($model)
    {
        // Define the relationship types that we want to automatically include in the Transformer
        $relations = [
            'hasMany',
            'hasManyThrough',
            //'belongsToMany',
            'hasOne',
            //'belongsTo',
            //'morphOne',
            'morphTo',
            'morphMany',
            //'morphToMany',
        ];

        // Generate new collection for relationships
        $relationships = new Collection();

        // Go through all the methods and relations and pick those that match the type
        foreach (get_class_methods($model) as $method) {
            // Get the contents of the method
            $methodContents = $this->getMethodContents($model, $method);

            foreach ($relations as $relation) {
                if (str_contains($methodContents, '$this->' . $relation) && $method !== 'morphedByMany') {
                    // Determine if the relation has one or many (item and collection in Fractal)
                    $relationCountType = (str_contains($relation, 'Many')) ? 'collection' : 'item';

                    // Add to collection
                    $relationships->push([
                        'method' => $method,
                        'relationType' => $relation,
                        'relationCountType' => $relationCountType,
                        'relatedClass' => $this->getRelationshipClassName($model, $method),
                    ]);
                }
            }
        }

        return $relationships;
    }

    /**
     * Return the methods contents as a string
     * @param  mixed  $model
     * @param  string $method
     * @return string
     */
    public function getMethodContents($model, $method)
    {
        // Use reflection to inspect the code, based on Illuminate/Support/SerializableClosure.php
        $reflection = new \ReflectionMethod($model, $method);
        $file = new \SplFileObject($reflection->getFileName());
        $file->seek($reflection->getStartLine() - 1);

        $code = '';

        while ($file->key() < $reflection->getEndLine()) {
            $code .= $file->current();
            $file->next();
        }

        $code = trim(preg_replace('/\s\s+/', '', $code));
        $begin = strpos($code, 'function(');
        $code = substr($code, $begin, strrpos($code, '}') - $begin + 1);

        return $code;
    }

    /**
     * The shortname of the class that the relationship is referring to
     * @param  mixed  $model
     * @param  string $method
     * @return string
     */
    public function getRelationshipClassName($model, $method)
    {
        // Create new model
        $model = new $model;
        $className = get_class($model->{$method}()->getRelated());

        $reflection = new \ReflectionClass($className);
        return $reflection->getShortName();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_REQUIRED, 'Name of the model we want to create the Transformer from'],
        ];
    }
}
