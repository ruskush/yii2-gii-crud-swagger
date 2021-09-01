<?php
/** @var $generator \ruskush\giiCrudSwagger\Generator */

$modelClassName = $generator->getModelClassName();
$definitionCollectionClassName = $generator->getDefinitionCollectionClassName();
$properties = $generator->getmodelFields();
echo "<?php\n";
?>

namespace <?=$generator->swaggerDefinitionsNamespace?>;
/**
 * @SWG\Definition(required={})
 * @SWG\Property (
 *     property="_items",
 *     type="array",
 *     description="Записи, возвращаемые запросом",
 *     @SWG\Items(ref="#/definitions/<?=$modelClassName?>")
 * ),
 * @SWG\Property (
 *     property="_links",
 *     type="object",
 *     description="Ссылки",
 *     @SWG\Property(
 *         property="self",
 *         type="object",
 *         @SWG\Property (property="href", type="string")
 *     )
 * ),
 * @SWG\Property (
 *     property="_meta",
 *     type="object",
 *     description="Пагинация",
 *     @SWG\Property(property="totalCount", type="integer"),
 *     @SWG\Property(property="pageCount", type="integer"),
 *     @SWG\Property(property="currentPage", type="integer"),
 *     @SWG\Property(property="perPage", type="integer"),
 * ),
 */
class <?=$definitionCollectionClassName?> {
    // dummy class for Swagger definitions
}