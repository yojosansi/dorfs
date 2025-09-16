<?php
// Punto de entrada principal de la aplicación
require_once 'includes/header.php';

?>

<?php require_once 'core/db_connect.php'; ?>

<div class="container mt-4">
    <div class="p-5 mb-4 bg-light rounded-3">
        <div class="container-fluid py-5">
            <h1 class="display-5 fw-bold">Gestor de Leyes Consolidadas</h1>
            <p class="col-md-8 fs-4">Bienvenido al sistema de gestión de leyes. Aquí puede encontrar y consultar la legislación consolidada.</p>
            <form class="d-flex" role="search" method="get" action="index.php">
                <input class="form-control me-2" type="search" name="q" placeholder="Buscar leyes por título o número..." aria-label="Search" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                <button class="btn btn-primary" type="submit">Buscar</button>
            </form>
        </div>
    </div>

    <?php
    try {
        $search_term = trim(filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING));
        $params = [];

        $sql = "SELECT
                    l.id, l.titulo, l.numero_ley, l.organismo, l.fecha_publicacion_inicial,
                    c.titulo as codigo_titulo
                FROM leyes l
                LEFT JOIN ley_codigo lc ON l.id = lc.ley_id
                LEFT JOIN codigos c ON lc.codigo_id = c.id";

        if ($search_term) {
            $sql .= " WHERE l.titulo LIKE :search_term OR l.numero_ley LIKE :search_term";
            $params[':search_term'] = '%' . $search_term . '%';
        }

        $sql .= " ORDER BY c.titulo ASC, l.titulo ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();

        $leyes_agrupadas = [];
        foreach ($results as $row) {
            $codigo_titulo = $row['codigo_titulo'] ?? 'Leyes sin Código Asignado';
            if (!isset($leyes_agrupadas[$codigo_titulo])) {
                $leyes_agrupadas[$codigo_titulo] = [];
            }
            $leyes_agrupadas[$codigo_titulo][$row['id']] = $row;
        }

        if (empty($results)) {
            echo '<div class="alert alert-info" role="alert">No hay leyes registradas en el sistema.</div>';
        } else {
            foreach ($leyes_agrupadas as $codigo_titulo => $leyes) {
                echo '<h2 class="mt-5 mb-3">' . htmlspecialchars($codigo_titulo) . '</h2>';
                echo '<div class="row row-cols-1 row-cols-md-2 g-4">';
                foreach ($leyes as $ley) {
                    echo '<div class="col">';
                    echo '  <div class="card h-100">';
                    echo '    <div class="card-body">';
                    echo '      <h5 class="card-title">' . htmlspecialchars($ley['titulo']) . '</h5>';
                    echo '      <p class="card-text"><strong>Número:</strong> ' . htmlspecialchars($ley['numero_ley']) . '</p>';
                    echo '      <p class="card-text"><strong>Organismo:</strong> ' . htmlspecialchars($ley['organismo']) . '</p>';
                    echo '      <a href="ver_ley.php?id=' . htmlspecialchars($ley['id']) . '" class="btn btn-primary">Ver Ley</a>';
                    echo '    </div>';
                    echo '    <div class="card-footer">';
                    echo '      <small class="text-muted">Publicada: ' . htmlspecialchars($ley['fecha_publicacion_inicial']) . '</small>';
                    echo '    </div>';
                    echo '  </div>';
                    echo '</div>';
                }
                echo '</div>';
            }
        }

    } catch (PDOException $e) {
        echo '<div class="alert alert-danger" role="alert">Error al conectar con la base de datos: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
    ?>
</div>

<?php
require_once 'includes/footer.php';
?>
