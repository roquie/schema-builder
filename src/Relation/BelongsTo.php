<?php

/**
 * Cycle ORM Schema Builder.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Cycle\Schema\Relation;

use Cycle\ORM\Relation;
use Cycle\Schema\Exception\RelationException;
use Cycle\Schema\InversableInterface;
use Cycle\Schema\Registry;
use Cycle\Schema\Relation\Traits\FieldTrait;
use Cycle\Schema\Relation\Traits\ForeignKeyTrait;
use Cycle\Schema\RelationInterface;

final class BelongsTo extends RelationSchema implements InversableInterface
{
    use FieldTrait;
    use ForeignKeyTrait;

    // internal relation type
    protected const RELATION_TYPE = Relation::BELONGS_TO;

    // relation schema options
    protected const RELATION_SCHEMA = [
        // save with parent
        Relation::CASCADE => true,

        // do not pre-load relation by default
        Relation::LOAD => Relation::LOAD_PROMISE,

        // nullable by default
        Relation::NULLABLE => false,

        // link to parent entity primary key by default
        Relation::INNER_KEY => '{relation}_{outerKey}',

        // default field name for inner key
        Relation::OUTER_KEY => '{target:primaryKey}',

        // rendering options
        RelationSchema::INDEX_CREATE => true,
        RelationSchema::FK_CREATE => true,
        RelationSchema::FK_ACTION => 'CASCADE',
    ];

    /**
     * @param Registry $registry
     */
    public function compute(Registry $registry): void
    {
        parent::compute($registry);

        $source = $registry->getEntity($this->source);
        $target = $registry->getEntity($this->target);

        // create target outer field
        $this->ensureField(
            $source,
            $this->options->get(Relation::INNER_KEY),
            $this->getField($target, Relation::OUTER_KEY),
            $this->options->get(Relation::NULLABLE)
        );
    }

    /**
     * @param Registry $registry
     */
    public function render(Registry $registry): void
    {
        $source = $registry->getEntity($this->source);
        $target = $registry->getEntity($this->target);

        $innerField = $this->getField($source, Relation::INNER_KEY);
        $outerField = $this->getField($target, Relation::OUTER_KEY);

        $table = $registry->getTableSchema($source);

        if ($this->options->get(self::INDEX_CREATE)) {
            $table->index([$innerField->getColumn()]);
        }

        if ($this->options->get(self::FK_CREATE)) {
            $this->createForeignKey($registry, $target, $source, $outerField, $innerField);
        }
    }

    /**
     * @param Registry $registry
     *
     * @return array
     */
    public function inverseTargets(Registry $registry): array
    {
        return [
            $registry->getEntity($this->target),
        ];
    }

    /**
     * @param RelationInterface $relation
     * @param string            $into
     * @param int|null          $load
     *
     * @throws RelationException
     *
     * @return RelationInterface
     */
    public function inverseRelation(RelationInterface $relation, string $into, ?int $load = null): RelationInterface
    {
        if (!$relation instanceof HasOne && !$relation instanceof HasMany) {
            throw new RelationException('BelongsTo relation can only be inversed into HasOne or HasMany');
        }

        return $relation->withContext(
            $into,
            $this->target,
            $this->source,
            $this->options->withOptions([
                Relation::LOAD => $load,
                Relation::INNER_KEY => $this->options->get(Relation::OUTER_KEY),
                Relation::OUTER_KEY => $this->options->get(Relation::INNER_KEY),
            ])
        );
    }
}
