<?php

namespace ruskush\giiCrudSwagger;

use Yii;
use yii\gii\CodeFile;
use yii\rest\Controller;

class Generator extends \yii\gii\generators\crud\Generator {
    /**
     * @var string
     */
    public $baseControllerClass = Controller::class;
    public $swaggerTag;
    public $module;

    /**
     * @inheritDoc
     */
    public function getName() {
        return 'CRUD API Swagger';
    }

    public function getDescription() {
        return 'Генерация кода для CRUD API контроллера с аннотациями Swagger';
    }
    public function rules() {
        return array_merge(parent::rules(), [[['module', 'swaggerTag'], 'safe']]);
    }

    public function hints() {
        return [
            'modelClass' => 'ActiveRecord класс, связанный с таблицей в БД, для которой генерируется CRUD API контроллер. Имя класса
                должно включать в себя пространство имён, например <code>app\models\Post</code>.',
            'controllerClass' => 'Имя контроллера, который должен быть сгенерирован. Имя должно включать в себя 
                пространство имён (например <code>app\controllers\PostController</code>), стиль имени -  CamelCase, 
                заканчиваться должно суффиксом <code>Controller</code>',
            'searchModelClass' => 'Класс search модели, для которой генерируется CRUD API контроллер. Имя класса
                должно включать в себя пространство имён, например <code>app\models\Post</code>',
            'baseControllerClass' => 'Класс контроллера, от которого будет наследоваться создаваемый CRUD контроллер.
            Имя класса должно включать в себя пространство имён, например <code>yii\rest\Controller</code>',
            'module' =>
                'Модуль, в котором создаётся контроллер. Параметр используется для генерации url в аннотациях Swagger.',
            'swaggerTag' => 'Значение параметра <code>tags={}</code> в аннотациях Swagger для группировки эндпоинтов',
        ];
    }

    public function stickyAttributes() {
        return ['baseControllerClass'];
    }

    /**
     * @inheritDoc
     */
    public function generate() {
        $controllerFile = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->controllerClass, '\\')) . '.php');

        $files = [new CodeFile($controllerFile, $this->render('controller.php'))];

        if (!empty($this->searchModelClass)) {
            $searchModel = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->searchModelClass, '\\') . '.php'));
            $files[] = new CodeFile($searchModel, $this->render('search.php'));
        }
        return $files;
    }

    /**
     * @return string[] список модулей в приложении, за исключением модулей gii и debug
     */
    public function getModules(): array {
        $modules = array_keys(Yii::$app->modules);
        $modules = array_flip($modules);
        unset($modules['gii'], $modules['debug']);
        return array_flip($modules);
    }

    /**
     * @return string Url for 'create' action
     */
    public function getCreateUrl(): string {
        return $this->getControllerUrlPart() . '/create';
    }

    /**
     * @return string Url for 'view' action
     */
    public function getViewUrl(): string {
        return $this->getControllerUrlPart() . '/view';
    }

    /**
     * @return string Url for 'list' action
     */
    public function getListUrl(): string {
        return $this->getControllerUrlPart() . '/list';
    }

    /**
     * @return string Url for 'update' action
     */
    public function getUpdateUrl(): string {
        return $this->getControllerUrlPart() . '/update';
    }

    /**
     * @return string Url for 'delete' action
     */
    public function getDeleteUrl(): string {
        return $this->getControllerUrlPart() . '/delete';
    }

    /**
     * @return string Url part with module ID (if exist) and with controller ID
     */
    protected function getControllerUrlPart(): string {
        return '/' . ($this->module ?? '') . '/' . $this->getControllerID();
    }
}
