<?php
/** @var $generator \ruskush\giiCrudSwagger\Generator */

$modelClassName = $generator->getModelClassName();
$properties = $generator->getmodelFields();
echo "<?php\n";
?>

namespace <?=$generator->swaggerDefinitionsNamespace?>;
/**
 * @SWG\Definition(required={})
<?php foreach ($properties as $property): ?>
 * @SWG\Property(property="<?=$property['name']?>", type="<?=$property['type']?>", description="<?=$property['comment']?>"),
<?php endforeach; ?>
 */
class <?=$modelClassName?> {
    // dummy class for Swagger definitions
}