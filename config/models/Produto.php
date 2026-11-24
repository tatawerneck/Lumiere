<?php
// Velas. Consultas da vitrine só trazem itens ativos em famílias ativas.
class Produto
{
    private PDO $db;

    // Ordenações permitidas (whitelist: nunca concatenar texto vindo do usuário).
    private const ORDENACOES = [
        'destaques'  => 'p.destaque DESC, p.novo DESC, p.id ASC',
        'recentes'   => 'p.id DESC',
        'nome'       => 'p.nome ASC',
        'preco_asc'  => 'p.preco ASC',
        'preco_desc' => 'p.preco DESC',
    ];

    private const BASE = 'SELECT p.*, c.nome AS categoria_nome, c.slug AS categoria_slug
                          FROM produto p JOIN categoria c ON c.id = p.categoria_id';

    public function __construct()
    {
        $this->db = Database::conexao();
    }

    public static function ordenacoesPermitidas(): array
    {
        return array_keys(self::ORDENACOES);
    }

    private function bindAll(PDOStatement $s, array $params): void
    {
        foreach ($params as $chave => $valor) {
            $s->bindValue($chave, $valor, is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
    }

    // Vitrine: filtros combináveis, ordenação e paginação.
    public function buscarLoja(array $f, int $pagina, int $porPagina): array
    {
        $where = ['p.ativo = 1', 'c.ativo = 1'];
        $params = [];
        if (($f['busca'] ?? '') !== '') {
            $where[] = '(p.nome LIKE :busca OR c.nome LIKE :busca OR p.notas_topo LIKE :busca
                         OR p.notas_coracao LIKE :busca OR p.notas_fundo LIKE :busca)';
            $params[':busca'] = '%' . $f['busca'] . '%';
        }
        if (!empty($f['familia'])) {
            $where[] = 'c.slug = :familia';
            $params[':familia'] = $f['familia'];
        }
        if (!empty($f['momento']) && isset(momentos_lista()[$f['momento']])) {
            $where[] = 'FIND_IN_SET(:momento, p.momentos) > 0';
            $params[':momento'] = $f['momento'];
        }
        if (isset($f['preco_max']) && $f['preco_max'] !== null) {
            $where[] = 'p.preco <= :pmax';
            $params[':pmax'] = (float)$f['preco_max'];
        }
        if (!empty($f['disponivel'])) {
            $where[] = 'p.estoque > 0';
        }
        if (!empty($f['promocao'])) {
            $where[] = 'p.preco_antigo IS NOT NULL AND p.preco_antigo > p.preco';
        }
        $ordem = self::ORDENACOES[$f['ordenar'] ?? 'destaques'] ?? self::ORDENACOES['destaques'];
        $clausula = 'FROM produto p JOIN categoria c ON c.id = p.categoria_id WHERE ' . implode(' AND ', $where);

        $total = $this->db->prepare('SELECT COUNT(*) ' . $clausula);
        $this->bindAll($total, $params);
        $total->execute();

        $itens = $this->db->prepare("SELECT p.*, c.nome AS categoria_nome, c.slug AS categoria_slug $clausula
                                     ORDER BY $ordem LIMIT :lim OFFSET :off");
        $this->bindAll($itens, $params);
        $itens->bindValue(':lim', $porPagina, PDO::PARAM_INT);
        $itens->bindValue(':off', max(0, ($pagina - 1) * $porPagina), PDO::PARAM_INT);
        $itens->execute();

        return ['itens' => $itens->fetchAll(), 'total' => (int)$total->fetchColumn()];
    }

    public function destaques(int $limite = 8): array
    {
        $s = $this->db->prepare(self::BASE . ' WHERE p.ativo = 1 AND c.ativo = 1
                                 ORDER BY p.destaque DESC, p.novo DESC, p.id ASC LIMIT :lim');
        $s->bindValue(':lim', $limite, PDO::PARAM_INT);
        $s->execute();
        return $s->fetchAll();
    }

    public function porMomento(string $momento, int $limite = 4): array
    {
        $s = $this->db->prepare(self::BASE . ' WHERE p.ativo = 1 AND c.ativo = 1 AND FIND_IN_SET(:m, p.momentos) > 0
                                 ORDER BY p.destaque DESC, p.id ASC LIMIT :lim');
        $s->bindValue(':m', $momento);
        $s->bindValue(':lim', $limite, PDO::PARAM_INT);
        $s->execute();
        return $s->fetchAll();
    }

    public function relacionados(int $produtoId, int $categoriaId, int $limite = 4): array
    {
        $s = $this->db->prepare(self::BASE . ' WHERE p.ativo = 1 AND c.ativo = 1 AND p.id <> :id
                                 ORDER BY (p.categoria_id = :cat) DESC, p.destaque DESC, RAND() LIMIT :lim');
        $s->bindValue(':id', $produtoId, PDO::PARAM_INT);
        $s->bindValue(':cat', $categoriaId, PDO::PARAM_INT);
        $s->bindValue(':lim', $limite, PDO::PARAM_INT);
        $s->execute();
        return $s->fetchAll();
    }

    public function buscarPublico(int $id): ?array
    {
        $s = $this->db->prepare(self::BASE . ' WHERE p.id = :id AND p.ativo = 1 AND c.ativo = 1');
        $s->execute([':id' => $id]);
        return $s->fetch() ?: null;
    }

    public function buscarPorId(int $id): ?array
    {
        $s = $this->db->prepare(self::BASE . ' WHERE p.id = :id');
        $s->execute([':id' => $id]);
        return $s->fetch() ?: null;
    }

    // Vários produtos de uma vez (carrinho). Retorna [id => produto].
    public function buscarVarios(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (!$ids) {
            return [];
        }
        $marcas = implode(',', array_fill(0, count($ids), '?'));
        $s = $this->db->prepare(self::BASE . " WHERE p.id IN ($marcas) AND p.ativo = 1");
        $s->execute($ids);
        $res = [];
        foreach ($s->fetchAll() as $p) {
            $res[(int)$p['id']] = $p;
        }
        return $res;
    }

    public function listarAdmin(string $busca = '', string $status = 'todos'): array
    {
        $where = [];
        $params = [];
        if ($busca !== '') {
            $where[] = '(p.nome LIKE :busca OR c.nome LIKE :busca)';
            $params[':busca'] = '%' . $busca . '%';
        }
        if ($status === 'ativos') {
            $where[] = 'p.ativo = 1';
        } elseif ($status === 'inativos') {
            $where[] = 'p.ativo = 0';
        } elseif ($status === 'estoque_baixo') {
            $where[] = 'p.estoque <= :baixo';
            $params[':baixo'] = (int)config('estoque_baixo');
        }
        $sql = self::BASE . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY p.nome';
        $s = $this->db->prepare($sql);
        $this->bindAll($s, $params);
        $s->execute();
        return $s->fetchAll();
    }

    // Grava dados do produto. O estoque muda por entradas e vendas (e pode ser ajustado no cadastro novo).
    public function salvar(array $d, ?int $id): int
    {
        $campos = [
            ':categoria_id'  => $d['categoria_id'],
            ':nome'          => $d['nome'],
            ':slug'          => $d['slug'],
            ':notas_topo'    => $d['notas_topo'],
            ':notas_coracao' => $d['notas_coracao'],
            ':notas_fundo'   => $d['notas_fundo'],
            ':descricao'     => $d['descricao'],
            ':quando_usar'   => $d['quando_usar'],
            ':momentos'      => $d['momentos'],
            ':peso_g'        => $d['peso_g'],
            ':queima_h'      => $d['queima_h'],
            ':preco'         => $d['preco'],
            ':preco_antigo'  => $d['preco_antigo'],
            ':destaque'      => $d['destaque'] ? 1 : 0,
            ':novo'          => $d['novo'] ? 1 : 0,
            ':ativo'         => $d['ativo'] ? 1 : 0,
            ':imagem_url'    => $d['imagem_url'],
            ':imagem2_url'   => $d['imagem2_url'],
        ];
        if ($id === null) {
            $campos[':estoque'] = $d['estoque'] ?? 0;
            $s = $this->db->prepare('INSERT INTO produto (categoria_id, nome, slug, notas_topo, notas_coracao, notas_fundo, descricao,
                    quando_usar, momentos, peso_g, queima_h, preco, preco_antigo, estoque, destaque, novo, ativo, imagem_url, imagem2_url)
                VALUES (:categoria_id, :nome, :slug, :notas_topo, :notas_coracao, :notas_fundo, :descricao, :quando_usar, :momentos,
                    :peso_g, :queima_h, :preco, :preco_antigo, :estoque, :destaque, :novo, :ativo, :imagem_url, :imagem2_url)');
            $s->execute($campos);
            return (int)$this->db->lastInsertId();
        }
        $campos[':id'] = $id;
        $s = $this->db->prepare('UPDATE produto SET categoria_id = :categoria_id, nome = :nome, slug = :slug, notas_topo = :notas_topo,
                notas_coracao = :notas_coracao, notas_fundo = :notas_fundo, descricao = :descricao, quando_usar = :quando_usar,
                momentos = :momentos, peso_g = :peso_g, queima_h = :queima_h, preco = :preco, preco_antigo = :preco_antigo,
                destaque = :destaque, novo = :novo, ativo = :ativo, imagem_url = :imagem_url, imagem2_url = :imagem2_url
            WHERE id = :id');
        $s->execute($campos);
        return $id;
    }

    public function slugDisponivel(string $slug, ?int $ignorarId): bool
    {
        $s = $this->db->prepare('SELECT COUNT(*) FROM produto WHERE slug = :s AND id <> :id');
        $s->execute([':s' => $slug, ':id' => $ignorarId ?? 0]);
        return (int)$s->fetchColumn() === 0;
    }

    public function definirImagem(int $id, ?string $ext): void
    {
        $s = $this->db->prepare('UPDATE produto SET imagem_ext = :ext WHERE id = :id');
        $s->execute([':ext' => $ext, ':id' => $id]);
    }

    public function alternarAtivo(int $id, bool $ativo): void
    {
        $s = $this->db->prepare('UPDATE produto SET ativo = :a WHERE id = :id');
        $s->execute([':a' => $ativo ? 1 : 0, ':id' => $id]);
    }

    // Quantos itens de pedido dependem do produto. Com algum, o produto não é excluído (só desativado).
    public function referencias(int $id): int
    {
        $s = $this->db->prepare('SELECT COUNT(*) FROM pedido_item WHERE produto_id = :id');
        $s->execute([':id' => $id]);
        return (int)$s->fetchColumn();
    }

    public function excluir(int $id): void
    {
        $this->db->prepare('DELETE FROM entrada WHERE produto_id = :id')->execute([':id' => $id]);
        $this->db->prepare('DELETE FROM produto WHERE id = :id')->execute([':id' => $id]);
    }

    public function estoqueBaixo(): array
    {
        $s = $this->db->prepare('SELECT id, nome, estoque FROM produto WHERE ativo = 1 AND estoque <= :b ORDER BY estoque, nome');
        $s->execute([':b' => (int)config('estoque_baixo')]);
        return $s->fetchAll();
    }

    public static function gerarSlug(string $texto): string
    {
        $t = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto) ?: $texto;
        $t = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $t) ?? '');
        return trim($t, '-') ?: 'vela';
    }
}
