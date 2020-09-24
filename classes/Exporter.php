<?php
use Illuminate\Support;
use LSS\Array2Xml;
require_once('classes/View.php');

// retrieves & formats data from the database for export
class Exporter {
    public function __construct() {
        $this->view = new View();
    }

    function getPlayerStats($search) {
        $where = [];
        if ($search->has('playerId')) 
            $where[] = "roster.id = '" . $search['playerId'] . "'";

        if ($search->has('player')) 
            $where[] = "roster.name = '" . $search['player'] . "'";

        if ($search->has('team')) 
            $where[] = "roster.team_code = '" . $search['team']. "'";

        if ($search->has('position')) 
            $where[] = "roster.pos = '" . $search['position'] . "'";

        if ($search->has('country')) 
            $where[] = "roster.nationality = '" . $search['country'] . "'";
       
        $where = implode(' AND ', $where);

        $sql = "SELECT roster.name, player_totals.*
                FROM player_totals
                INNER JOIN roster ON (roster.id = player_totals.player_id)
                WHERE $where";

            $data = query($sql) ?: [];
        
            foreach ($data as &$row) {
                unset($row['player_id']);
                $row['total_points'] = ($row['3pt'] * 3) + ($row['2pt'] * 2) + $row['free_throws'];
                $row['field_goals_pct'] = $row['field_goals_attempted'] ? (round($row['field_goals'] / $row['field_goals_attempted'], 2) * 100) . '%' : 0;
                $row['3pt_pct'] = $row['3pt_attempted'] ? (round($row['3pt'] / $row['3pt_attempted'], 2) * 100) . '%' : 0;
                $row['2pt_pct'] = $row['2pt_attempted'] ? (round($row['2pt'] / $row['2pt_attempted'], 2) * 100) . '%' : 0;
                $row['free_throws_pct'] = $row['free_throws_attempted'] ? (round($row['free_throws'] / $row['free_throws_attempted'], 2) * 100) . '%' : 0;
                $row['total_rebounds'] = $row['offensive_rebounds'] + $row['defensive_rebounds'];
            }
          
        return collect($data);
    }

    function getPlayers($search) {
        $where = [];
        if ($search->has('playerId')) 
            $where[] = "roster.id = '" . $search['playerId'] . "'";

        if ($search->has('player')) 
            $where[] = "roster.name = '" . $search['player'] . "'";

        if ($search->has('team')) 
            $where[] = "roster.team_code = '" . $search['team']. "'";

        if ($search->has('position')) 
            $where[] = "roster.position = '" . $search['position'] . "'";

        if ($search->has('country')) 
            $where[] = "roster.nationality = '" . $search['country'] . "'";

        $where = implode(' AND ', $where);

        $sql = "SELECT roster.*
                FROM roster
                WHERE $where";

        return collect(query($sql))
            ->map(function($item, $key) {
                unset($item['id']);
                return $item;
            });
    }

    public function format($data, $format = 'html') {
        
        // return the right data format
        switch($format) {
            case 'xml':
                echo $this->renderXml($data);
                break;
            case 'json':
                header('Content-type: application/json');
                echo json_encode($data->all());
                break;
            case 'csv':
                echo $this->renderCSV($data);
                break;
            default: // html
                
                if (!$data->count()) {
                    $_SESSION['flash'] = 'Sorry, no matching data was found';
                }
                
                // extract headings
                // replace underscores with space & ucfirst each word for a decent heading
                $headings = collect($data->get(0))->keys();
                
                $headings = $headings->map(function($item, $key) {
                    return collect(explode('_', $item))
                        ->map(function($item, $key) {
                            return ucfirst($item);
                        })
                        ->join(' ');
                });
              
                $this->view->renderHtml('index', ['headings' => $headings, 'rows' => $data]);

                break;
        }
    }

    ###################### HELPER FUNCTIONS ###############################
    private function renderXml($data)
    {
        header('Content-type: text/xml');
                
        // fix any keys starting with numbers
        $keyMap = ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
        $xmlData = [];
        foreach ($data->all() as $row) {
            $xmlRow = [];
            foreach ($row as $key => $value) {
                $key = preg_replace_callback('(\d)', function($matches) use ($keyMap) {
                    return $keyMap[$matches[0]] . '_';
                }, $key);
                $xmlRow[$key] = $value;
            }
            $xmlData[] = $xmlRow;
        }
        $xml = Array2XML::createXML('data', [
            'entry' => $xmlData
        ]);
        return $xml->saveXML();
    }


    private function renderCSV($data)
    {
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="export.csv";');
        if (!$data->count()) {
            return;
        }
        $csv = [];
        
        // extract headings
        // replace underscores with space & ucfirst each word for a decent headings
        $headings = collect($data->get(0))->keys();
        $headings = $headings->map(function($item, $key) {
            return collect(explode('_', $item))
                ->map(function($item, $key) {
                    return ucfirst($item);
                })
                ->join(' ');
        });
        $csv[] = $headings->join(',');

        // format data
        foreach ($data as $dataRow) {
            $csv[] = implode(',', array_values($dataRow));
        }
        return implode("\n", $csv);
    }
}

?>