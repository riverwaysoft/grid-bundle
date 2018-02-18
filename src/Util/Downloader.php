<?php

namespace Riverway\Grid\Util;

use Box\Spout\Common\Type;
use Box\Spout\Writer\WriterFactory;
use Riverway\Grid\Widget\GridWidget;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Downloader
{

    public static function download(Request $request, GridWidget $grid, $name = 'report'): ?Response
    {
        if ($request->query->get('download')) {
            $name = $name.(new \DateTime())->format('Y-m-d');

            ini_set('memory_limit', '1024M');
            set_time_limit(180);
            $response = new StreamedResponse();
            $name .= '.csv';
            $response->headers->add([
                'Content-Type' => 'application/csv; charset=utf-8',
                'Content-Disposition' => "attachment; filename='{$name}'",
            ]);
            if (strstr($request->headers->get('user-agent'), 'Macintosh')) {
                $delimiter = ',';
            } else {
                $delimiter = ';';
            }
            $response->setCallback(function () use ($name, $grid, $delimiter) {
                $grid->disablePagination();
                $gridData = $grid->generateGridData(true);
                $writer = WriterFactory::create(Type::CSV);
                $writer->setFieldDelimiter($delimiter);
                $writer->openToBrowser($name);
                $writer->addRow($gridData->getTitles());

                foreach ($gridData->getBody() as $row) {
                    if (is_array($row['values'])) {
                        $writer->addRow($row['values']);
                    }
                }
                $writer->close();
            });

            return $response;
        }

        return null;
    }
}