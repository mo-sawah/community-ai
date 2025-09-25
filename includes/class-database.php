<?php
if (!defined('ABSPATH')) exit;

class AI_Community_Database {
    const DB_VERSION = '1.0.0';
    private $posts_table;
    private $comments_table;
    private $votes_table;
    private $communities_table;
    private $user_meta_table;

    public function __construct() {
        global $wpdb;
        $prefix = $wpdb->prefix . 'community_ai_';
        $this->posts_table = $prefix . 'posts';
        $this->comments_table = $prefix . 'comments';
        $this->votes_table = $prefix . 'votes';
        $this->communities_table = $prefix . 'communities';
        $this->user_meta_table = $prefix . 'user_meta';
    }

    public function create_tables() {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $charset_collate = $wpdb->get_charset_collate();

        $sqls = [
            "CREATE TABLE {$this->posts_table} (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                author_id BIGINT UNSIGNED NOT NULL,
                community_id BIGINT UNSIGNED NOT NULL,
                title TEXT NOT NULL,
                content LONGTEXT NOT NULL,
                excerpt TEXT,
                tags TEXT,
                status VARCHAR(20) NOT NULL DEFAULT 'published',
                votes INT NOT NULL DEFAULT 0,
                comment_count INT UNSIGNED NOT NULL DEFAULT 0,
                view_count INT UNSIGNED NOT NULL DEFAULT 0,
                is_ai_generated BOOLEAN NOT NULL DEFAULT 0,
                ai_model VARCHAR(100),
                source_url VARCHAR(255),
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY author_id (author_id),
                KEY community_id (community_id),
                KEY status (status),
                FULLTEXT KEY content_search (title, content, excerpt)
            ) $charset_collate;",
            "CREATE TABLE {$this->communities_table} (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(100) NOT NULL,
                description TEXT,
                color VARCHAR(7) DEFAULT '#6366f1',
                icon VARCHAR(255),
                post_count INT UNSIGNED NOT NULL DEFAULT 0,
                member_count INT UNSIGNED NOT NULL DEFAULT 0,
                created_by BIGINT UNSIGNED NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY slug (slug)
            ) $charset_collate;"
        ];

        foreach ($sqls as $sql) {
            dbDelta($sql);
        }

        update_option('community_ai_db_version', self::DB_VERSION);
    }
    
    public function get_posts(array $args = []) {
        global $wpdb;
        
        $defaults = [
            'per_page' => 10,
            'page' => 1,
            'community' => '',
            'sort' => 'hot',
            'search' => '',
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $sql = "SELECT p.*, u.display_name as author_name, c.name as community_name, c.slug as community_slug, c.color as community_color
                FROM {$this->posts_table} p
                LEFT JOIN {$wpdb->users} u ON p.author_id = u.ID
                LEFT JOIN {$this->communities_table} c ON p.community_id = c.id
                WHERE p.status = %s";
                
        $params = ['published'];
        
        if (!empty($args['community'])) {
            $sql .= " AND c.slug = %s";
            $params[] = $args['community'];
        }

        if (!empty($args['search'])) {
            $sql .= " AND MATCH(p.title, p.content, p.excerpt) AGAINST(%s IN BOOLEAN MODE)";
            $params[] = '*' . $args['search'] . '*';
        }
        
        if ($args['sort'] === 'hot') {
            // A simple "hot" ranking algorithm (votes over time)
            $sql .= " ORDER BY (p.votes / POW(TIMESTAMPDIFF(HOUR, p.created_at, NOW()) + 2, 1.8)) DESC";
        } elseif ($args['sort'] === 'new') {
            $sql .= " ORDER BY p.created_at DESC";
        } elseif ($args['sort'] === 'top') {
            $sql .= " ORDER BY p.votes DESC";
        }
        
        $offset = ($args['page'] - 1) * $args['per_page'];
        $sql .= " LIMIT %d OFFSET %d";
        $params[] = $args['per_page'];
        $params[] = $offset;

        return $wpdb->get_results($wpdb->prepare($sql, $params));
    }
    
    public function get_posts_count(array $args = []) {
        // ... a simplified version of get_posts to get the count
        return 100; // Placeholder
    }

    public function create_post(array $data) {
        global $wpdb;

        $community_id = $this->get_community_id_by_slug($data['community_slug']);
        if (!$community_id) {
            return false; // Or handle error
        }

        $result = $wpdb->insert(
            $this->posts_table,
            [
                'author_id' => $data['author_id'],
                'community_id' => $community_id,
                'title' => $data['title'],
                'content' => $data['content'],
                'tags' => $data['tags'] ?? '',
                'is_ai_generated' => $data['is_ai_generated'] ?? 0,
                'ai_model' => $data['ai_model'] ?? '',
                'source_url' => $data['source_url'] ?? '',
                'status' => $data['status'] ?? 'published'
            ]
        );

        if ($result) {
            $this->update_community_post_count($community_id);
            return $wpdb->insert_id;
        }

        return false;
    }
    
    public function create_community(array $data) {
        global $wpdb;
        return $wpdb->insert($this->communities_table, $data);
    }
    
    public function get_community_id_by_slug(string $slug) {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->communities_table} WHERE slug = %s", $slug));
    }

    private function update_community_post_count(int $community_id) {
        global $wpdb;
        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->posts_table} WHERE community_id = %d AND status = 'published'", $community_id));
        $wpdb->update($this->communities_table, ['post_count' => $count], ['id' => $community_id]);
    }

    public function get_table_names() {
        return [
            'posts' => $this->posts_table,
            'comments' => $this->comments_table,
            'votes' => $this->votes_table,
            'communities' => $this->communities_table,
            'user_meta' => $this->user_meta_table,
        ];
    }
}