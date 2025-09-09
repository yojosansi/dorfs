<?php
require_once 'core/db_connect.php';
// The user has placed the library here
require_once 'libs/fpdf/fpdf.php';

// 1. Obtener y validar IDs de la URL
$ley_id = filter_input(INPUT_GET, 'ley_id', FILTER_VALIDATE_INT);
$version_id = filter_input(INPUT_GET, 'version_id', FILTER_VALIDATE_INT);

if (!$ley_id || !$version_id) {
    header('Content-Type: text/plain; charset=utf-8');
    die('Error: Parámetros inválidos. Se requiere un ID de ley y un ID de versión.');
}

// 2. Obtener datos de la base de datos
try {
    // Obtener información principal de la ley y la versión
    $stmt_info = $pdo->prepare(
        "SELECT l.titulo, l.numero_ley, v.titulo_version, v.fecha_version
         FROM leyes l
         JOIN versiones v ON l.id = v.ley_id
         WHERE l.id = :ley_id AND v.id = :version_id"
    );
    $stmt_info->execute(['ley_id' => $ley_id, 'version_id' => $version_id]);
    $info = $stmt_info->fetch();

    if (!$info) {
        die('No se encontró la ley o la versión especificada.');
    }

    // Obtener el contenido completo (artículos y sus secciones)
    $stmt_contenido = $pdo->prepare(
        "SELECT a.numero_articulo, a.titulo_articulo, s.tipo_seccion, s.identificador_seccion, s.contenido
         FROM articulos a
         LEFT JOIN secciones_articulo s ON a.id = s.articulo_id
         WHERE a.version_id = :version_id
         ORDER BY a.orden, a.id, s.orden, s.id"
    );
    $stmt_contenido->execute(['version_id' => $version_id]);
    $contenido_rows = $stmt_contenido->fetchAll();

    // Agrupar el contenido por artículo para un renderizado más sencillo
    $articulos = [];
    foreach ($contenido_rows as $row) {
        $key = 'art-' . $row['numero_articulo'];
        if (!isset($articulos[$key])) {
            $articulos[$key] = [
                'numero' => $row['numero_articulo'],
                'titulo' => $row['titulo_articulo'],
                'secciones' => []
            ];
        }
        if ($row['contenido']) {
            $articulos[$key]['secciones'][] = $row;
        }
    }

} catch (PDOException $e) {
    die("Error de base de datos: " . $e->getMessage());
}

// 3. Crear clase PDF personalizada para un diseño profesional
class PDF_Ley extends FPDF
{
    private $ley_titulo_header = '';

    function setLeyTitulo($titulo) {
        // Usamos utf8_decode para la compatibilidad con las fuentes base de FPDF
        $this->ley_titulo_header = utf8_decode($titulo);
    }

    // Cabecera de página
    function Header()
    {
        $this->SetFont('Arial','B',10);
        $this->Cell(0, 10, $this->ley_titulo_header, 0, 0, 'C');
        $this->Ln(15); // Un poco más de espacio después del header
    }

    // Pie de página
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    // Título principal del documento
    function TituloDocumento($titulo_ley, $numero_ley, $titulo_version, $fecha_version)
    {
        $this->SetFont('Times','B',20);
        $this->MultiCell(0, 10, utf8_decode($titulo_ley), 0, 'C');
        $this->SetFont('Times','',14);
        $this->MultiCell(0, 8, utf8_decode("Número de Ley: $numero_ley"), 0, 'C');
        $this->Ln(5);
        $this->SetFont('Arial','BI',12);
        $this->MultiCell(0, 8, utf8_decode("Versión consolidada: $titulo_version (al " . date('d/m/Y', strtotime($fecha_version)) . ")"), 0, 'C');
        $this->Ln(12);
    }

    // Renderiza un artículo completo
    function Articulo($numero, $titulo, $secciones)
    {
        $this->SetFont('Times','B',14);
        $this->SetFillColor(220, 220, 220); // Un gris claro para destacar
        $this->Cell(0, 8, utf8_decode("Artículo $numero. " . ($titulo ?: '')), 0, 1, 'L', true);
        $this->Ln(5);

        $this->SetFont('Times','',12);
        if (empty($secciones)) {
            $this->SetFont('Times','I',12);
            $this->MultiCell(0, 7, utf8_decode("[Este artículo no tiene contenido detallado registrado]"));
            $this->Ln(5);
        } else {
            foreach ($secciones as $seccion) {
                // Combinar tipo e identificador para el título de la sección
                $titulo_seccion = trim(utf8_decode($seccion['tipo_seccion'] . ' ' . $seccion['identificador_seccion']));

                $this->SetFont('Times','B',12);
                $this->MultiCell(0, 7, $titulo_seccion . '.');

                $this->SetFont('Times','',12);
                $this->MultiCell(0, 7, utf8_decode($seccion['contenido']));
                $this->Ln(4);
            }
        }
    }
}

// 4. Creación e inicialización del documento PDF
$pdf = new PDF_Ley('P', 'mm', 'A4');
$pdf->setLeyTitulo($info['titulo']);
$pdf->AliasNbPages(); // Permite usar {nb} para el total de páginas en el Footer
$pdf->SetMargins(20, 25, 20); // Márgenes (izq, arriba, der)
$pdf->AddPage();

// 5. Renderizado del contenido en el PDF
$pdf->TituloDocumento($info['titulo'], $info['numero_ley'], $info['titulo_version'], $info['fecha_version']);

foreach ($articulos as $articulo) {
    $pdf->Articulo($articulo['numero'], $articulo['titulo'], $articulo['secciones']);
}

// 6. Salida del PDF al navegador
// 'I' para inline, 'D' para forzar descarga, 'F' para guardar en fichero
$safe_title = preg_replace('/[^a-zA-Z0-9_-]/', '_', $info['titulo']);
$pdf->Output('I', $safe_title . '.pdf');
?>
