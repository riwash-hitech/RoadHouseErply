<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDO;

class DBConnectionController extends Controller
{
    public function index()
    {
        return view('dbConnectionForm');
    }

    public function deConnectioncheck(Request $request)
    {
        $host = $request->host;
        $userName = $request->username;
        $password = $request->password;
        // $database='UBA_Online';
        $database = $request->dbName;
        try {
            $connection = new PDO("sqlsrv:Server=$host;database=$database", $userName, $password);
            // $stmt = $connection->query('SELECT TOP 5  * FROM ERPLY_ItemMaster_DEV');
            // $products = $stmt->fetchAll();
            return response()->json(['status' => 'success', 'data' => '', 'message' => 'connection Successfull'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Connection failed: ' . $e->getMessage()], 200);
        }
    }
}
