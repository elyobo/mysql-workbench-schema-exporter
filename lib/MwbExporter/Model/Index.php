<?php

/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 * Copyright (c) 2012 Toha <tohenk@yahoo.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace MwbExporter\Model;

class Index extends Base
{
    /**
     * @var array
     */
    protected $columns = array();

    protected function init()
    {
        // iterate on column configuration
        foreach ($this->node->value as $key => $node) {
            $attributes = $node->attributes();
            $this->parameters->set((string) $attributes['key'], (string) $node[0]);
        }
        // check for primary columns, to notify column
        $nodes = $this->node->xpath("value[@key='columns']/value/link[@key='referencedColumn']");
        $isSingle = count($nodes) == 1;
        foreach ($nodes as $node) {
            // for primary indexes ignore external index
            // definition and set column to primary instead
            if (!($column = $this->getDocument()->getReference()->get((string) $node))) {
                continue;
            }

            if ($isSingle) {
                // Columns in composite keys should not be marked primary or unique
                if ($this->isPrimary()) {
                    $column->markAsPrimary();
                }
                if ($this->isUnique()) {
                    $column->markAsUnique();
                }
            }

            $this->columns[] = $column;
        }
        if (!$this->isPrimary() && ($table = $this->getDocument()->getReference()->get((string) $this->node->link))) {
            $table->injectIndex($this);
        }
    }

    /**
     * Is a unique index
     *
     * @return boolean
     */
    public function isUnique()
    {
        return $this->parameters->get('indexType') === 'UNIQUE' ? true : false;
    }

    /**
     * Is a normal index
     *
     * @return boolean
     */
    public function isIndex()
    {
        return $this->parameters->get('indexType') === 'INDEX' ? true : false;
    }

    /**
     * Is a primary index
     *
     * @return boolean
     */
    public function isPrimary()
    {
        return $this->parameters->get('indexType') === 'PRIMARY' ? true : false;
    }

    /**
     * Get index columns.
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }
}
