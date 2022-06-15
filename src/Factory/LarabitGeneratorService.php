<?php

namespace App\Factory;

class LarabitGeneratorService
{
    private $dbConn;
    private $database;

    private $targetExportConfig = __DIR__ . '/../../../../../config/';
    private $targetExportSrc = __DIR__ . '/../../../../../src/';

    private $sourceFactory = __DIR__ . '/../../src/Factory/';

    private $allTables = array();

    public function __construct($dbConn, $database)
    {
        $this->dbConn = $dbConn;
        $this->database = $database;
    }

    public function generateStructure()
    {
        $this->validateHasDatabase();

        $this->updateFilesRequiredToConfig();

        $this->generateControllerFilesByTable();
        $this->generateEntityFilesByTable();
        $this->generateExceptionFilesByTable();
        $this->generateRepositoryFilesByTable();
        $this->generateRouteFilesByTable();
        $this->generateServiceContainerFilesByTable();
        $this->generateServiceFilesByTable();
    }

    function validateHasDatabase()
    {
        $tables_in_db = $this->dbConn::select('SHOW TABLES');
        $db = "Tables_in_".$this->database;
        $tableList = array();
        foreach($tables_in_db as $table){
            $dataTable = $this->dbConn::select('describe ' . $table->{$db});
            // the field list array
            $fieldList = array();

            // array pointer
            $i = 0;
            // loop through the generated field list
            foreach ($dataTable as $fVal) {
                $data = new \stdClass();
                $data->key = $fVal->Field;
                $data->data = $fVal;
                // put it into the array
                $fieldList[$i] = $data;
                $i++;
            }
            // after completing the loop, put the results into the table list array
            $tableList[$table->{$db}] = $fieldList;
        }
        $this->allTables = $tableList;
    }

    function doQuery($sqlStatment, $indexField = 'auto')
    {
        // perform the query and put it into a temporary variable
        $dbQuery = $this->dbConn::select($sqlStatment);
        // create an array of queried objects
        $dataSet = array();

        // this variable is used for automatic counter
        $i = 0;

        // Structuring the internal table for the data
        // loop through the records retrieved
        while ($rows = $dbQuery->fetch()) {

            if ($indexField != 'auto') {                    // if not automatic indexing

                // use the specified indexField specified
                // as index pointer and assign the value retrived from the database
                $dataSet[$rows[$indexField]] = $rows;

            } else {                                        // if automatic indexing
                // assign current index count as a pointer for the current
                // data retrived form the databse.
                $dataSet[$i] = $rows;
                // increase index counter
                $i++;
            }

        }

        return $dataSet;
    }


    function updateFilesRequiredToConfig()
    {

        /**
         * All repositories
         */

        $__configRepositories = PHP_EOL;
        $__configRepositories .= PHP_EOL;
        foreach ($this->allTables as $index => $table) {
            $__configRepositories .= "use App\Repository\\" . ucfirst($index) . "Repository;" . PHP_EOL;
        }

        $__configRepositories .= PHP_EOL;

        foreach ($this->allTables as $index => $table) {
            $__configRepositories .= "\$container['" . $index . "_repository'] = static function () {" . PHP_EOL;
            $__configRepositories .= "    return new " . ucfirst($index) . "Repository();" . PHP_EOL;
            $__configRepositories .= "};" . PHP_EOL;
        }

        $__configRepositories .= PHP_EOL;

        $__configRepositories = "<?php " . $__configRepositories . "?>";

        /**
         * All routes
         */

        $__configRoutes = PHP_EOL;
        $__configRoutes .= PHP_EOL;

        $__configRoutes .= "\$app->get('/', 'App\Controller\DefaultController:getHelp');" . PHP_EOL;
        $__configRoutes .= "\$app->get('/status', 'App\Controller\DefaultController:getStatus');" . PHP_EOL;
        $__configRoutes .= PHP_EOL;
        $__configRoutes .= "\$app->group('/api', function () use (\$app) {" . PHP_EOL;

        foreach ($this->allTables as $index => $table) {
            $__configRoutes .= "    require __DIR__ . '/../src/Route/" . $index . "_route.php'; " . PHP_EOL;
        }

        $__configRoutes .= "});" . PHP_EOL;
        $__configRoutes .= PHP_EOL;

        $__configRoutes = "<?php " . $__configRoutes . "?>";

        /**
         * All services
         */

        $__configServices = PHP_EOL;
        $__configServices .= PHP_EOL;

        foreach ($this->allTables as $index => $table) {
            $__configServices .= "require __DIR__ . '/../src/Service/" . $index . "_service.php';" . PHP_EOL;
        }

        $__configServices .= PHP_EOL;

        $__configServices = "<?php " . $__configServices . "?>";

        /**
         * Default controller
         */

        $__srcControllerDefault = PHP_EOL;
        $__srcControllerDefault .= PHP_EOL;
        $__srcControllerDefault .= "namespace App\Controller;" . PHP_EOL;
        $__srcControllerDefault .= PHP_EOL;
        $__srcControllerDefault .= "final class DefaultController extends BaseController" . PHP_EOL;
        $__srcControllerDefault .= "{" . PHP_EOL;
        $__srcControllerDefault .= "    const API_VERSION = '1.0.0';" . PHP_EOL;
        $__srcControllerDefault .= PHP_EOL;
        $__srcControllerDefault .= "    public function getHelp(\$request, \$response)" . PHP_EOL;
        $__srcControllerDefault .= "    {" . PHP_EOL;
        $__srcControllerDefault .= "        \$app = \$this->container->get('settings')['app'];" . PHP_EOL;
        $__srcControllerDefault .= "        \$url = \$app['domain'];" . PHP_EOL;
        $__srcControllerDefault .= "        \$endpoints = [" . PHP_EOL;
        foreach ($this->allTables as $index => $table) {
            $__srcControllerDefault .= "            '" . $index . "' => \$url . '/api/" . $index . "'," . PHP_EOL;
        }
        $__srcControllerDefault .= "            'status' => \$url . '/status'," . PHP_EOL;
        $__srcControllerDefault .= "            'this help' => \$url . ''," . PHP_EOL;
        $__srcControllerDefault .= "        ];" . PHP_EOL;
        $__srcControllerDefault .= "        \$data = [" . PHP_EOL;
        $__srcControllerDefault .= "            'endpoints' => \$endpoints," . PHP_EOL;
        $__srcControllerDefault .= "            'version' => self::API_VERSION," . PHP_EOL;
        $__srcControllerDefault .= "            'timestamp' => time()," . PHP_EOL;
        $__srcControllerDefault .= "        ];" . PHP_EOL;
        $__srcControllerDefault .= PHP_EOL;
        $__srcControllerDefault .= "        return \$this->jsonResponse(\$response, 'success', \$data, 200);" . PHP_EOL;
        $__srcControllerDefault .= "    }" . PHP_EOL;
        $__srcControllerDefault .= PHP_EOL;
        $__srcControllerDefault .= "    public function getStatus(\$request, \$response)" . PHP_EOL;
        $__srcControllerDefault .= "    {" . PHP_EOL;
        $__srcControllerDefault .= "        \$status = [" . PHP_EOL;
        $__srcControllerDefault .= "            'stats' => \$this->getDbStats()," . PHP_EOL;
        $__srcControllerDefault .= "            'MySQL' => 'OK'," . PHP_EOL;
        $__srcControllerDefault .= "            'version' => self::API_VERSION," . PHP_EOL;
        $__srcControllerDefault .= "            'timestamp' => time()," . PHP_EOL;
        $__srcControllerDefault .= "        ];" . PHP_EOL;
        $__srcControllerDefault .= PHP_EOL;
        $__srcControllerDefault .= "        return \$this->jsonResponse(\$response, 'success', \$status, 200);" . PHP_EOL;
        $__srcControllerDefault .= "    }" . PHP_EOL;
        $__srcControllerDefault .= PHP_EOL;
        $__srcControllerDefault .= "    private function getDbStats()" . PHP_EOL;
        $__srcControllerDefault .= "    {" . PHP_EOL;
        foreach ($this->allTables as $index => $table) {
            $__srcControllerDefault .= "        \$" . $index . "Service = \$this->container->get('find_" . $index . "_service');" . PHP_EOL;
        }
        $__srcControllerDefault .= PHP_EOL;
        $__srcControllerDefault .= "        return [" . PHP_EOL;
        foreach ($this->allTables as $index => $table) {
            $__srcControllerDefault .= "            '" . $index . "s' => count(\$" . $index . "Service->getAll())," . PHP_EOL;
        }
        $__srcControllerDefault .= "        ];" . PHP_EOL;
        $__srcControllerDefault .= "    }" . PHP_EOL;
        $__srcControllerDefault .= "}" . PHP_EOL;

        $__srcControllerDefault = "<?php " . $__srcControllerDefault . "?>";

        $this->_writeFile($__configRepositories, $this->targetExportConfig . "repositories.php");
        $this->_writeFile($__configRoutes, $this->targetExportConfig . "routes.php");
        $this->_writeFile($__configServices, $this->targetExportConfig . "services.php");
        $this->_writeFile($__srcControllerDefault, $this->targetExportSrc . "Controller/DefaultController.php");
    }

    function generateControllerFilesByTable()
    {
        $source = $this->sourceFactory . 'TemplateBase/ObjectbaseController';

        foreach ($this->allTables as $index => $table) {
            $target = $this->targetExportSrc . 'Controller/' . ucfirst($index);
            $this->rcopy($source, $target);

            // Replace CRUD Controller Template for New Entity.
            $this->replaceFileContent($target . '/Base.php', $index);
            $this->replaceFileContent($target . '/Create.php', $index);
            $this->replaceFileContent($target . '/Delete.php', $index);
            $this->replaceFileContent($target . '/GetAll.php', $index);
            $this->replaceFileContent($target . '/GetOne.php', $index);
            $this->replaceFileContent($target . '/Update.php', $index);
        }

    }

    function generateEntityFilesByTable()
    {

        foreach ($this->allTables as $indexTable => $table) {
            $__srcEntity = PHP_EOL;
            $__srcEntity .= PHP_EOL;
            $__srcEntity .= "namespace App\Entity;" . PHP_EOL;
            $__srcEntity .= PHP_EOL;
            $__srcEntity .= "use \Illuminate\Database\Eloquent\Model;" . PHP_EOL;
            $__srcEntity .= PHP_EOL;
            $__srcEntity .= "final class " . ucfirst($indexTable) . " extends Model" . PHP_EOL;
            $__srcEntity .= "{" . PHP_EOL;

            $__srcEntity .= "    protected \$table = '" . $indexTable . "';" . PHP_EOL;
            $__srcEntity .= PHP_EOL;
            $__srcEntity .= "    protected \$primaryKey = '" . $indexTable . "_id';" . PHP_EOL;
            $__srcEntity .= PHP_EOL;
            $__srcEntity .= "    public \$timestamps = false;" . PHP_EOL;
            $__srcEntity .= PHP_EOL;
            $__srcEntity .= "    protected \$fillable = [" . PHP_EOL;
            foreach ($table as $indexField => $field) {
                $__srcEntity .= "        '" . $table[$indexField]->key . "'," . PHP_EOL;
            }
            $__srcEntity .= "    ];" . PHP_EOL;

            $__srcEntity .= PHP_EOL;

            foreach ($table as $indexField => $field) {
                $field = $table[$indexField]->key;
                $__srcEntity .= "    public function get" . ucwords($field) . "(){ " . PHP_EOL;
                $__srcEntity .= "        return \$this->getAttribute('" . $field . "');" . PHP_EOL;
                $__srcEntity .= "    }" . PHP_EOL;
                $__srcEntity .= PHP_EOL;
                if ($field != $indexTable . '_id') {
                    $__srcEntity .= "    public function set" . ucwords($field) . "($" . $field . "){ " . PHP_EOL;
                    $__srcEntity .= "        \$this->setAttribute('" . $field . "', \$" . $field . ");" . PHP_EOL;
                    $__srcEntity .= "    }" . PHP_EOL;
                    $__srcEntity .= PHP_EOL;
                }
            }
            $__srcEntity .= "}" . PHP_EOL;

            $__srcEntity = "<?php " . $__srcEntity . "?>";

            @mkdir($this->targetExportSrc . 'Entity');

            $this->_writeFile($__srcEntity, $this->targetExportSrc . "Entity/" . ucfirst($indexTable) . ".php");
        }
    }

    function generateExceptionFilesByTable()
    {
        $source = $this->sourceFactory . 'TemplateBase/ObjectbaseException.php';
        foreach ($this->allTables as $index => $table) {
            $target = $this->targetExportSrc . 'Exception/' . ucfirst($index) . '.php';
            @mkdir($this->targetExportSrc . 'Exception');
            copy($source, $target);
            $this->replaceFileContent($target, $index);
        }

    }

    function generateRepositoryFilesByTable()
    {
        $source = $this->sourceFactory . 'TemplateBase/ObjectbaseRepository.php';
        foreach ($this->allTables as $index => $table) {
            $target = $this->targetExportSrc . 'Repository/' . ucfirst($index) . 'Repository.php';
            @mkdir($this->targetExportSrc . 'Repository');
            copy($source, $target);
            $this->replaceFileContent($target, $index);
        }

    }

    function generateRouteFilesByTable()
    {
        $source = $this->sourceFactory . 'TemplateBase/ObjectbaseRoute.php';
        foreach ($this->allTables as $index => $table) {
            $target = $this->targetExportSrc . 'Route/' . $index . '_route.php';
            @mkdir($this->targetExportSrc . 'Route');
            copy($source, $target);
            $this->replaceFileContent($target, $index);
        }

    }

    function generateServiceContainerFilesByTable()
    {
        $source = $this->sourceFactory . 'TemplateBase/ObjectbaseService.php';
        foreach ($this->allTables as $index => $table) {
            $target = $this->targetExportSrc . 'Service/' . $index . '_service.php';
            @mkdir($this->targetExportSrc . 'Service');
            copy($source, $target);
            $this->replaceFileContent($target, $index);
        }
    }

    function generateServiceFilesByTable()
    {
        foreach ($this->allTables as $indexTable => $table) {

            $tableWithEmail = false;
            $tableWithDescription = false;
            $tableWithName = false;
            $tableWithStatus = false;

            // perform fields and accessor generation
            foreach ($table as $indexField => $field) {
                $field = $table[$indexField]->key;
                if (strpos($field, 'correo')) {
                    $tableWithEmail = true;
                    break;
                }
                if (strpos($field, 'descripcion')) {
                    $tableWithDescription = true;
                    break;
                }
                if (strpos($field, 'nombre')) {
                    $tableWithName = true;
                    break;
                }
                if (strpos($field, 'estado')) {
                    $tableWithStatus = true;
                    break;
                }
            }

            @mkdir($this->targetExportSrc . "Service/" . ucwords($indexTable));

            /**
             * Base file
             */

            $__srcService = PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "namespace App\Service\\" . ucfirst($indexTable) . ";" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "use App\Repository\\" . ucfirst($indexTable) . "Repository;" . PHP_EOL;
            $__srcService .= "use App\Service\BaseService;" . PHP_EOL;
            $__srcService .= "use App\Exception\\" . ucfirst($indexTable) . " as " . ucfirst($indexTable) . "Exception;" . PHP_EOL;
            $__srcService .= "use Respect\Validation\Validator as validator;" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "abstract class Base extends BaseService" . PHP_EOL;
            $__srcService .= "{" . PHP_EOL;
            $__srcService .= "    public \$" . $indexTable . "Repository;" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "    public function __construct(" . ucfirst($indexTable) . "Repository \$" . $indexTable . "Repository)" . PHP_EOL;
            $__srcService .= "    {" . PHP_EOL;
            $__srcService .= "        \$this->" . $indexTable . "Repository = \$" . $indexTable . "Repository;" . PHP_EOL;
            $__srcService .= "    }" . PHP_EOL;
            $__srcService .= PHP_EOL;
            if ($tableWithName) {
                $__srcService .= "    protected static function validar" . ucfirst($indexTable) . "Nombre(\$name)" . PHP_EOL;
                $__srcService .= "    {" . PHP_EOL;
                $__srcService .= "        if (!validator::length(1, 50)->validate(\$name)) {" . PHP_EOL;
                $__srcService .= "            throw new " . ucfirst($indexTable) . "Exception('El nombre es inválido.', 400);" . PHP_EOL;
                $__srcService .= "        }" . PHP_EOL;
                $__srcService .= PHP_EOL;
                $__srcService .= "        return \$name;" . PHP_EOL;
                $__srcService .= "    }" . PHP_EOL;
                $__srcService .= PHP_EOL;
            }
            if ($tableWithDescription) {
                $__srcService .= "    protected static function validar" . ucfirst($indexTable) . "Descripcion(\$description)" . PHP_EOL;
                $__srcService .= "    {" . PHP_EOL;
                $__srcService .= "        if (!validator::length(1, 50)->validate(\$description)) {" . PHP_EOL;
                $__srcService .= "            throw new " . ucfirst($indexTable) . "Exception('La descripcion es inválida.', 400);" . PHP_EOL;
                $__srcService .= "        }" . PHP_EOL;
                $__srcService .= PHP_EOL;
                $__srcService .= "        return \$description;" . PHP_EOL;
                $__srcService .= "    }" . PHP_EOL;
                $__srcService .= PHP_EOL;
            }
            if ($tableWithEmail) {
                $__srcService .= "protected static function validar" . ucfirst($indexTable) . "Correo(\$emailValue)" . PHP_EOL;
                $__srcService .= "{" . PHP_EOL;
                $__srcService .= "    \$email = filter_var(\$emailValue, FILTER_SANITIZE_EMAIL);" . PHP_EOL;
                $__srcService .= "    if (!validator::email()->validate(\$email)) {" . PHP_EOL;
                $__srcService .= "        throw new " . ucfirst($indexTable) . "Exception('Correo invalido', 400);" . PHP_EOL;
                $__srcService .= "     }" . PHP_EOL;
                $__srcService .= PHP_EOL;
                $__srcService .= "     return (string)\$email;" . PHP_EOL;
                $__srcService .= "}" . PHP_EOL;
                $__srcService .= PHP_EOL;
            }
            if ($tableWithStatus) {
                $__srcService .= "protected static function validate" . ucfirst($indexTable) . "Estado(\$status)" . PHP_EOL;
                $__srcService .= "{" . PHP_EOL;
                $__srcService .= "    if (!validator::numeric()->between(0, 1)->validate(\$status)) {" . PHP_EOL;
                $__srcService .= "       throw new " . ucfirst($indexTable) . "Exception('Estado invalido', 400);" . PHP_EOL;
                $__srcService .= "    }" . PHP_EOL;
                $__srcService .= PHP_EOL;
                $__srcService .= "    return \$status;" . PHP_EOL;
                $__srcService .= "}" . PHP_EOL;
                $__srcService .= PHP_EOL;
            }
            $__srcService .= "    protected function get" . ucfirst($indexTable) . "FromCache(\$" . $indexTable . "Id)" . PHP_EOL;
            $__srcService .= "    {" . PHP_EOL;
            $__srcService .= "        return \$this->get" . ucfirst($indexTable) . "FromDb(\$" . $indexTable . "Id)->toJson();" . PHP_EOL;
            $__srcService .= "    }" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "    protected function get" . ucfirst($indexTable) . "FromDb(\$" . $indexTable . "Id)" . PHP_EOL;
            $__srcService .= "    {" . PHP_EOL;
            $__srcService .= "        return \$this->" . $indexTable . "Repository->checkAndGet" . ucfirst($indexTable) . "OrFail(\$" . $indexTable . "Id);" . PHP_EOL;
            $__srcService .= "    }" . PHP_EOL;
            $__srcService .= "}" . PHP_EOL;

            $__srcService = "<?php " . $__srcService . "?>";

            $this->_writeFile($__srcService, $this->targetExportSrc . "Service/" . ucwords($indexTable) . "/Base.php");

            /**
             * Create file
             */

            $__srcService = PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "namespace App\Service\\" . ucwords($indexTable) . ";" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "use App\Exception\\" . ucwords($indexTable) . " as " . ucwords($indexTable) . "Exception;" . PHP_EOL;
            $__srcService .= "use App\Entity\\" . ucwords($indexTable) . ";" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "final class Create extends Base" . PHP_EOL;
            $__srcService .= "{" . PHP_EOL;
            $__srcService .= "    public function create(\$input)" . PHP_EOL;
            $__srcService .= "    {" . PHP_EOL;
            $__srcService .= "        \$data = \$this->validate" . ucwords($indexTable) . "Data(\$input);" . PHP_EOL;
            $__srcService .= "        return \$this->" . $indexTable . "Repository->create(\$data);" . PHP_EOL;
            $__srcService .= "    }" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "    private function validate" . ucwords($indexTable) . "Data(\$input)" . PHP_EOL;
            $__srcService .= "    {" . PHP_EOL;
            $__srcService .= "        \$" . $indexTable . " = json_decode((string)json_encode(\$input), false);" . PHP_EOL;
            $__srcService .= "        \$hasException = false;" . PHP_EOL;
            $__srcService .= "        \$fieldsException = array();" . PHP_EOL;
            $__srcService .= PHP_EOL;
            foreach ($table as $indexField => $field) {
                $field = $table[$indexField]->key;
                $data = $table[$indexField]->data;
                if ($data->Null == 'NO' && $data->Key != 'PRI') {
                    $__srcService .= "        if (!isset(\$" . $indexTable . "->" . $field . ")) {" . PHP_EOL;
                    $__srcService .= "            \$hasException = true;" . PHP_EOL;
                    $__srcService .= "            \$fieldsException[] = '" . $field . "';" . PHP_EOL;
                    $__srcService .= "        }" . PHP_EOL;
                }
            }
            $__srcService .= PHP_EOL;
            $__srcService .= "        if (\$hasException == true) {" . PHP_EOL;
            $__srcService .= "            throw new " . ucwords($indexTable) . "Exception('El/los campos ' . implode(',', \$fieldsException) . ' son requerido(s).', 400);" . PHP_EOL;
            $__srcService .= "        }" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "        \$" . $indexTable . "ToCreate = new " . ucwords($indexTable) . "();" . PHP_EOL;
            $__srcService .= PHP_EOL;
            /*foreach ($table as $indexField => $field) {
                $field = $table[$indexField]->key;
                $data = $table[$indexField]->data;
                if ($data->Key != 'PRI') {
                    $__srcService .= "        if (isset(\$" . $indexTable . "->" . $field . ")) {" . PHP_EOL;
                    $__srcService .= "            \$" . $indexTable . "ToCreate->set" . ucwords($field) . "(\$" . $indexTable . "->" . $field . ");" . PHP_EOL;
                    $__srcService .= "        }" . PHP_EOL;
                }
            }
            $__srcService .= PHP_EOL;
            */
            $__srcService .= "        return \$" . $indexTable . "ToCreate->setRawAttributes((array) \$" . $indexTable . ");" . PHP_EOL;
            $__srcService .= "    }" . PHP_EOL;
            $__srcService .= "}" . PHP_EOL;

            $__srcService = "<?php " . $__srcService . "?>";

            $this->_writeFile($__srcService, $this->targetExportSrc . "Service/" . ucwords($indexTable) . "/Create.php");

            /**
             * Delete file
             */

            $__srcService = PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "namespace App\Service\\" . ucwords($indexTable) . ";" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "final class Delete extends Base" . PHP_EOL;
            $__srcService .= "{" . PHP_EOL;
            $__srcService .= "    public function delete(\$" . $indexTable . "Id)" . PHP_EOL;
            $__srcService .= "    {" . PHP_EOL;
            $__srcService .= "        \$" . $indexTable . " = \$this->get" . ucwords($indexTable) . "FromDb(\$" . $indexTable . "Id);" . PHP_EOL;
            $__srcService .= "        \$this->" . $indexTable . "Repository->delete(\$" . $indexTable . ");" . PHP_EOL;
            $__srcService .= "        return \$" . $indexTable . ";" . PHP_EOL;
            $__srcService .= "    }" . PHP_EOL;
            $__srcService .= "}" . PHP_EOL;

            $__srcService = "<?php " . $__srcService . "?>";

            $this->_writeFile($__srcService, $this->targetExportSrc . "Service/" . ucwords($indexTable) . "/Delete.php");

            /**
             * Find file
             */

            $__srcService = PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "namespace App\Service\\" . ucwords($indexTable) . ";" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "final class Find extends Base" . PHP_EOL;
            $__srcService .= "{" . PHP_EOL;
            $__srcService .= "    public function get" . ucwords($indexTable) . "sByPage(\$page, \$perPage)" . PHP_EOL;
            $__srcService .= "    {" . PHP_EOL;
            $__srcService .= "        if (\$page < 1) {" . PHP_EOL;
            $__srcService .= "            \$page = 1;" . PHP_EOL;
            $__srcService .= "        }" . PHP_EOL;
            $__srcService .= "        if (\$perPage < 1) {" . PHP_EOL;
            $__srcService .= "            \$perPage = self::DEFAULT_PER_PAGE_PAGINATION;" . PHP_EOL;
            $__srcService .= "        }" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "        return \$this->" . $indexTable . "Repository->get" . ucwords($indexTable) . "sByPage(" . PHP_EOL;
            $__srcService .= "            \$page," . PHP_EOL;
            $__srcService .= "            \$perPage" . PHP_EOL;
            $__srcService .= "        );" . PHP_EOL;
            $__srcService .= "    }" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "    public function getAll()" . PHP_EOL;
            $__srcService .= "    {" . PHP_EOL;
            $__srcService .= "        return \$this->" . $indexTable . "Repository->getAll();" . PHP_EOL;
            $__srcService .= "    }" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "    public function get" . ucwords($indexTable) . "(\$" . $indexTable . "Id)" . PHP_EOL;
            $__srcService .= "    {" . PHP_EOL;
            $__srcService .= "        return \$this->get" . ucwords($indexTable) . "FromDb(\$" . $indexTable . "Id);" . PHP_EOL;
            $__srcService .= "    }" . PHP_EOL;
            $__srcService .= "}" . PHP_EOL;

            $__srcService = "<?php " . $__srcService . "?>";

            $this->_writeFile($__srcService, $this->targetExportSrc . "Service/" . ucwords($indexTable) . "/Find.php");

            /**
             * Update file
             */

            $__srcService = PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "namespace App\Service\\" . ucwords($indexTable) . ";" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "use App\Entity\\" . ucwords($indexTable) . ";" . PHP_EOL;
            $__srcService .= "use \App\Exception\\" . ucwords($indexTable) . " as " . ucwords($indexTable) . "Exception;" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "final class Update extends Base" . PHP_EOL;
            $__srcService .= "{" . PHP_EOL;
            $__srcService .= "    public function update(\$input, \$" . $indexTable . "Id)" . PHP_EOL;
            $__srcService .= "    {" . PHP_EOL;
            $__srcService .= "        \$data = \$this->validate" . ucwords($indexTable) . "Data(\$input, \$" . $indexTable . "Id);" . PHP_EOL;
            $__srcService .= "        return \$this->" . $indexTable . "Repository->update(\$data);" . PHP_EOL;
            $__srcService .= "    }" . PHP_EOL;
            $__srcService .= PHP_EOL;
            $__srcService .= "    private function validate" . ucwords($indexTable) . "Data(\$input, \$" . $indexTable . "Id)" . PHP_EOL;
            $__srcService .= "    {" . PHP_EOL;
            $__srcService .= "        \$" . $indexTable . "ToUpdate = \$this->get" . ucwords($indexTable) . "FromDb(\$" . $indexTable . "Id);" . PHP_EOL;
            $__srcService .= "        \$data = json_decode((string)json_encode(\$input), false);" . PHP_EOL;
            $__srcService .= "        \$hasException = false;" . PHP_EOL;
            $__srcService .= "        \$fieldsException = array();" . PHP_EOL;
            $__srcService .= PHP_EOL;
            foreach ($table as $indexField => $field) {
                $field = $table[$indexField]->key;
                $data = $table[$indexField]->data;
                if ($data->Null == 'NO' && $data->Key != 'PRI') {
                    $__srcService .= "        if (!isset(\$data->" . $field . ")) {" . PHP_EOL;
                    $__srcService .= "            \$hasException = true;" . PHP_EOL;
                    $__srcService .= "            \$fieldsException[] = '" . $field . "';" . PHP_EOL;
                    $__srcService .= "        }" . PHP_EOL;
                }
            }
            $__srcService .= PHP_EOL;
            $__srcService .= "        if (\$hasException == true) {" . PHP_EOL;
            $__srcService .= "            throw new " . ucwords($indexTable) . "Exception('El/los campos ' . implode(',', \$fieldsException) . ' son requerido(s).', 400);" . PHP_EOL;
            $__srcService .= "        }" . PHP_EOL;
            $__srcService .= PHP_EOL;
            /*foreach ($table as $indexField => $field) {
                $field = $table[$indexField]->key;
                $data = $table[$indexField]->data;
                if ($data->Key != 'PRI') {
                    $__srcService .= "        if (isset(\$data->" . $field . ")) {" . PHP_EOL;
                    $__srcService .= "            \$" . $indexTable . "ToUpdate->set" . ucwords($field) . "(\$data->" . $field . ");" . PHP_EOL;
                    $__srcService .= "        }" . PHP_EOL;
                }
            }
            $__srcService .= PHP_EOL;
            */
            $__srcService .= "        return \$" . $indexTable . "ToUpdate->setRawAttributes((array) \$data);" . PHP_EOL;
            $__srcService .= "    }" . PHP_EOL;
            $__srcService .= "}" . PHP_EOL;

            $__srcService = "<?php " . $__srcService . "?>";

            $this->_writeFile($__srcService, $this->targetExportSrc . "Service/" . ucwords($indexTable) . "/Update.php");

        }
    }

    private function rcopy($source, $target)
    {
        if (is_dir($source)) {
            @mkdir($target);
            $d = dir($source);
            while (FALSE !== ($entry = $d->read())) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                $Entry = $source . '/' . $entry;
                if (is_dir($Entry)) {
                    $this->rcopy($Entry, $target . '/' . $entry);
                    continue;
                }
                copy($Entry, $target . '/' . $entry);
            }

            $d->close();
        } else {
            copy($source, $target);
        }
    }

    function _writeFile($fClass, $fName)
    {

        if (!$handle = fopen($fName, 'w')) {

            exit;
        }

        if (fwrite($handle, $fClass) === FALSE) {
            exit;
        }
        fclose($handle);

    }

    private function replaceFileContent($target, $replacement, $valueToChange = 'objectbase')
    {
        $content1 = file_get_contents($target);
        if ($valueToChange == 'objectbase') {
            $content2 = preg_replace("/" . 'Objectbase' . "/", ucfirst($replacement), $content1);
            $content3 = preg_replace("/" . 'objectbase' . "/", $replacement, $content2);
        } else {
            $content3 = preg_replace("/" . $valueToChange . "/", $replacement, $content1);
        }
        file_put_contents($target, $content3);
    }

}
