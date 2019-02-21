<?php
declare(strict_types=1);
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Cycle\Schema\Definition\Map;

use Cycle\Schema\Definition\Field;
use Cycle\Schema\Exception\FieldException;

/**
 * Manage the set of fields associated with the entity.
 */
final class FieldMap
{
    /** @var Field[] */
    private $fields = [];

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->fields[$name]);
    }

    /**
     * @param string $name
     * @return Field
     */
    public function get(string $name): Field
    {
        if (!$this->has($name)) {
            throw new FieldException("Undefined field `{$name}`");
        }

        return $this->fields[$name];
    }

    /**
     * @param string $name
     * @param Field  $field
     * @return FieldMap
     */
    public function set(string $name, Field $field): self
    {
        if ($this->has($name)) {
            throw new FieldException("Field `{$name}` already exists");
        }

        $this->fields[$name] = $field;

        return $this;
    }

    /**
     * Get named list of all fields.
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->fields;
    }
}