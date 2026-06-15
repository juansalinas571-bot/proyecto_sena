<?php // Inicia el bloque de código PHP

namespace App\Controllers; // Define el espacio de nombres para controladores

class Reports extends BaseController // Controlador para la sección de reportes de acceso
{
    // Muestra los reportes de acceso con filtros, estadísticas y paginación
    public function index()
    {
        $logModel = model('App\Models\AccessLogModel'); // Carga el modelo de logs de acceso

        // Obtiene los filtros desde la URL (query string)
        $dateFrom = $this->request->getGet('date_from'); // Fecha inicial del filtro
        $dateTo   = $this->request->getGet('date_to');   // Fecha final del filtro
        $status   = $this->request->getGet('status');    // Estado: success o denied
        $search   = $this->request->getGet('search');    // Búsqueda por nombre o documento

        // ====== ESTADÍSTICAS GLOBALES ======
        $total = $logModel->countAll(); // Total de registros en access_logs

        // Totales de todos los tiempos
        $totalSuccess = $logModel->where('status', 'success')->countAllResults(); // Total exitosos histórico
        $totalDenied = $logModel->where('status', 'denied')->countAllResults(); // Total denegados histórico

        // Estadísticas de hoy
        $today = $logModel // Accesos de hoy
            ->where('DATE(created_at)', date('Y-m-d')) // Filtra por fecha actual
            ->countAllResults();
        $successToday = $logModel // Accesos exitosos de hoy
            ->where('DATE(created_at)', date('Y-m-d'))
            ->where('status', 'success')
            ->countAllResults();
        $deniedToday = $today - $successToday; // Accesos denegados hoy (por diferencia)

        // ====== CONSULTA CON FILTROS ======
        $logModel->orderBy('created_at', 'DESC'); // Ordena por fecha descendente

        // Aplica filtro por fecha desde
        if ($dateFrom) {
            $logModel->where('created_at >=', $dateFrom . ' 00:00:00'); // Desde el inicio del día
        }
        // Aplica filtro por fecha hasta
        if ($dateTo) {
            $logModel->where('created_at <=', $dateTo . ' 23:59:59'); // Hasta el final del día
        }
        // Aplica filtro por estado
        if ($status && in_array($status, ['success', 'denied'])) {
            $logModel->where('status', $status);
        }
        // Aplica búsqueda por texto (nombre o documento)
        if ($search) {
            $logModel
                ->groupStart() // Agrupa condiciones con OR
                ->like('name', $search) // Busca en nombre
                ->orLike('num_doc', $search) // Busca en documento
                ->groupEnd();
        }

        $totalFiltered = $logModel->countAllResults(false); // Cuenta resultados filtrados (sin reiniciar query)

        // ====== PAGINACIÓN ======
        $page    = max(1, (int) $this->request->getGet('page')); // Página actual
        $perPage = 25; // Registros por página
        $offset  = ($page - 1) * $perPage; // Desplazamiento

        $logs = $logModel->findAll($perPage, $offset); // Obtiene los registros de la página actual

        // Renderiza la vista con todos los datos
        return view('reports/index', [
            'title'         => 'Reportes de Acceso', // Título de la página
            'user'          => service('session')->get('user_data'), // Usuario logueado
            'logs'          => $logs, // Registros de la página actual
            'total'         => $total, // Total global
            'totalFiltered' => $totalFiltered, // Total después de filtros
            'today'         => $today, // Accesos hoy
            'successToday'  => $successToday, // Exitosos hoy
            'deniedToday'   => $deniedToday, // Denegados hoy
            'totalSuccess'  => $totalSuccess, // Total exitosos histórico
            'totalDenied'   => $totalDenied, // Total denegados histórico
            'page'          => $page, // Página actual
            'totalPages'    => (int) ceil($totalFiltered / $perPage), // Total de páginas
            'dateFrom'      => $dateFrom, // Filtro fecha desde (para mantener en el formulario)
            'dateTo'        => $dateTo, // Filtro fecha hasta
            'statusFilter'  => $status, // Filtro estado
            'searchQuery'   => $search, // Filtro búsqueda
        ]);
    }
}
