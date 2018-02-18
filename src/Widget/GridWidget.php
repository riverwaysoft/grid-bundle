<?php

namespace Riverway\Grid\Widget;

use Doctrine\ORM\Query;
use Knp\Component\Pager\PaginatorInterface;
use Riverway\Grid\Util\Downloader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Created by PhpStorm.
 * User: kate
 * Date: 25.11.16
 * Time: 13:34.
 */
class GridWidget
{
    private $translator;
    private $paginator;
    /**
     * @var Request
     */
    private $request;

    private $fields = [];
    private $pa;
    /**
     * @var Query
     */
    private $query;
    private $isPaginate;
    private $rowAttr;

    public function __construct(
        PaginatorInterface $paginator,
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
        $this->paginator = $paginator;
        $this->isPaginate = true;
        $this->pa = PropertyAccess::createPropertyAccessor();
    }

    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    public function setQuery(Query $query)
    {
        $query->setMaxResults(5000);
        $this->query = $query;
    }

    public function disablePagination()
    {
        $this->isPaginate = false;
    }

    public function setRequest(RequestStack $request_stack)
    {
        $this->request = $request_stack->getCurrentRequest();
    }

    public function getGridParams(): array
    {
        $paginator = $this->paginator;
        $pagination = $paginator->paginate(
            $this->query,
            $this->request->query->getInt('page', 1),
            $this->isPaginate ? 50 : 100000
        );

        $gridDO = $this->generateGridData();
        $gridParams = [
            'head' => $gridDO->getHead(),
            'body' => $gridDO->getBody(),
            'pagination' => $pagination,
            'paginate' => $this->isPaginate,
        ];

        return $gridParams;
    }

    /**
     * @param bool $asReport
     *
     * @return GridDataObject
     */
    public function generateGridData(bool $asReport = false): GridDataObject
    {
        $fields = $this->fields;
        $head = [];
        $body = [];

        foreach ($fields as $fieldName => $field) {
            if (is_array($field)) {
                $headStr = [
                    'attr' => $fieldName,
                    'title' => isset($field['label']) ? $field['label'] : $this->translate($fieldName),
                    'sortable' => isset($field['sortable']) ? $field['sortable'] : false,
                ];
            } else {
                $headStr = [
                    'attr' => $field,
                    'title' => $this->translate($field),
                    'sortable' => false,
                ];
            }
            $head[$fieldName] = $headStr;
        }

        $models = $this->query->getResult();
        foreach ($models as $i => $model) {
            $bodyRow = [];
            foreach ($fields as $fieldName => $field) {
                if (is_array($field)) {
                    if (!$this->preventWalk($field, $fieldName, $head, $model, $asReport)) {
                        continue;
                    }
                    if (isset($field['value'])) {
                        if (is_callable($field['value'])) {
                            $value = call_user_func_array($field['value'], [$model, $i, $asReport]);
                        } else {
                            $value = ($field['value']);
                        }
                    } else {
                        $value = $this->pa->getValue($model, $fieldName);
                    }
                    if (isset($field['translate']) && $field['translate'] === true) {
                        $value = $this->translate($value);
                    } elseif (isset($field['humanize']) && $field['humanize'] === true) {
                        $value = $this->humanize($value);
                    }
                } else {
                    $value = $this->pa->getValue($model, $field);
                }
                $bodyRow[] = $value;
            }
            $bodyTmp['values'] = $bodyRow;
            $bodyTmp['attr'] = [];
            if (!$asReport && $this->rowAttr) {
                if ($this->rowAttr instanceof \Closure) {
                    $bodyTmp['attr'] = call_user_func_array($this->rowAttr, [$model]);
                } else {
                    $bodyTmp['attr'] = $this->rowAttr;
                }
            }
            $body[] = $bodyTmp;
        }

        return (new GridDataObject())->setHead($head)->setBody($body);
    }

    /**
     * @param \Closure|array $attr
     */
    public function setRowAttr($attr)
    {
        $this->rowAttr = $attr;
    }

    private function humanize(string $key): string
    {
        $res = explode('_', strtolower($key));
        foreach ($res as $i => $val) {
            $res[$i] = ucfirst($val);
        }

        return join(' ', $res);
    }

    private function translate(string $key): string
    {
        return $this->translator->trans($key);
    }

    public function tryToDownload($name = 'report'): ?Response
    {
        $grid = clone $this;
        return Downloader::download($this->request, $grid, $name);
    }

    private function preventWalk(array $field, string $fieldName, array &$head, $model, bool $asReport): bool
    {
        if (isset($field['is_visible'])) {
            if (!call_user_func_array($field['is_visible'], [$model])) {
                if (isset($head[$fieldName])) {
                    unset($head[$fieldName]);
                }

                return false;
            }
        }
        if (isset($field['no_report']) && $asReport) {
            if (isset($head[$fieldName])) {
                unset($head[$fieldName]);
            }

            return false;
        }
        if (isset($field['report_only']) && !$asReport) {
            if (isset($head[$fieldName])) {
                unset($head[$fieldName]);
            }

            return false;
        }

        return true;
    }
}
