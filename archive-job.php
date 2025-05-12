<?php
/**
 * 求人アーカイブ・検索結果表示用テンプレート
 */
get_header();

// クエリ変数から検索条件を取得
$location_slug = get_query_var('job_location');
$position_slug = get_query_var('job_position');
$job_type_slug = get_query_var('job_type');
$facility_type_slug = get_query_var('facility_type');
$job_feature_slug = get_query_var('job_feature');
$features_only = get_query_var('job_features_only');

// URLクエリパラメータから特徴の配列を取得（複数選択の場合）
$feature_slugs = isset($_GET['features']) ? (array)$_GET['features'] : array();

// 特徴のスラッグが単一で指定されている場合、それも追加
if (!empty($job_feature_slug) && !in_array($job_feature_slug, $feature_slugs)) {
    $feature_slugs[] = $job_feature_slug;
}

// カスタムクエリを構築
$tax_query = array();

if (!empty($location_slug)) {
    $tax_query[] = array(
        'taxonomy' => 'job_location',
        'field'    => 'slug',
        'terms'    => $location_slug,
    );
}

if (!empty($position_slug)) {
    $tax_query[] = array(
        'taxonomy' => 'job_position',
        'field'    => 'slug',
        'terms'    => $position_slug,
    );
}

if (!empty($job_type_slug)) {
    $tax_query[] = array(
        'taxonomy' => 'job_type',
        'field'    => 'slug',
        'terms'    => $job_type_slug,
    );
}

if (!empty($facility_type_slug)) {
    $tax_query[] = array(
        'taxonomy' => 'facility_type',
        'field'    => 'slug',
        'terms'    => $facility_type_slug,
    );
}

// 特徴の配列が空でない場合、tax_queryに追加
if (!empty($feature_slugs)) {
    $tax_query[] = array(
        'taxonomy' => 'job_feature',
        'field'    => 'slug',
        'terms'    => $feature_slugs,
        'operator' => 'IN',
    );
}

// tax_queryが複数ある場合はAND条件に設定
if (count($tax_query) > 1) {
    $tax_query['relation'] = 'AND';
}

// 検索条件を表示用にまとめる
$conditions = array();

if (!empty($location_slug)) {
    $location_term = get_term_by('slug', $location_slug, 'job_location');
    if ($location_term) {
        $conditions[] = $location_term->name;
    }
}

if (!empty($position_slug)) {
    $position_term = get_term_by('slug', $position_slug, 'job_position');
    if ($position_term) {
        $conditions[] = $position_term->name;
    }
}

if (!empty($job_type_slug)) {
    $job_type_term = get_term_by('slug', $job_type_slug, 'job_type');
    if ($job_type_term) {
        $conditions[] = $job_type_term->name;
    }
}

if (!empty($facility_type_slug)) {
    $facility_type_term = get_term_by('slug', $facility_type_slug, 'facility_type');
    if ($facility_type_term) {
        $conditions[] = $facility_type_term->name;
    }
}

// 特徴の表示名を取得
$feature_names = array();
foreach ($feature_slugs as $slug) {
    $feature_term = get_term_by('slug', $slug, 'job_feature');
    if ($feature_term) {
        $feature_names[] = $feature_term->name;
        if (!in_array($feature_term->name, $conditions)) {
            $conditions[] = $feature_term->name;
        }
    }
}

// カスタムクエリを実行
if (!empty($tax_query)) {
    // メインクエリを変更
    global $wp_query;
    
    // 現在のページネーション情報を保持
    $paged = get_query_var('paged') ? get_query_var('paged') : 1;
    
    $args = array(
        'post_type' => 'job',
        'posts_per_page' => 10,
        'paged' => $paged,
        'tax_query' => $tax_query,
    );
    
    // 検索キーワードがある場合
    $search_query = get_search_query();
    if (!empty($search_query)) {
        $args['s'] = $search_query;
        $conditions[] = '"' . $search_query . '"';
    }
    
    // クエリを上書き
    $wp_query = new WP_Query($args);
}
?>

<style>
/* サイドバー非表示用のスタイル */
#sidebar, .sidebar, #secondary {
    display: none !important;
}
#main, .main, #primary, .content-area, .container {
    width: 100% !important;
    max-width: 100% !important;
    float: none !important;
    margin-left: auto !important;
    margin-right: auto !important;
}

/* 雇用形態に基づくカラー設定 */
.employment-type {
    background-color: #90CAF9; /* デフォルト色 */
    color: white;
    padding: 6px 15px;
    border-radius: 30px; /* より丸みを増す */
    font-size: 14px;
    margin-left: auto;
    display: inline-block;
}
.employment-type.full-time-color {
    background-color: #4CAF50 !important; /* 正社員 */
}
.employment-type.part-time-color {
    background-color: #FFC107 !important; /* パート・アルバイト */
}
.employment-type.contract-color {
    background-color: #9C27B0 !important; /* 契約社員 */
}
.employment-type.temporary-color {
    background-color: #FF5722 !important; /* 派遣社員 */
}

/* 施設アイコン関連スタイル */
.facility-icons {
    display: flex;
    gap: 10px;
    margin-top: 10px;
    margin-bottom: 10px;
}
.facility-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    background-color: #fff;
}
	
.facility-icon.red-icon {
    border-color: #FF5252;
}

.facility-icon img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
/* 施設アイコンのテキストを非表示 */
.facility-icon span {
    display: none;
}

/* 求人カードのセンター寄せと幅調整 */
.job-list, .job-cards-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
}

/* コンテナ幅の統一 */
.job-listing-container,
.container,
.content-wrapper .container {
    width: 100%;
	max-width: 1000px;
    margin-left: auto;
    margin-right: auto;
}

/* 現在の検索条件の表示 */
.current-filters {
    margin: 20px 0;
    padding: 15px;
    background-color: #f8f8f8;
    border-radius: 8px;
}

.filter-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}

.filter-tag {
    display: inline-flex;
    align-items: center;
    background-color: #e0e0e0;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 14px;
}

.filter-tag .remove-filter {
    margin-left: 5px;
    cursor: pointer;
    color: #666;
}

.filter-tag .remove-filter:hover {
    color: #f00;
}
</style>

<script>
// DOM読み込み後にサイドバーを強制的に非表示
document.addEventListener('DOMContentLoaded', function() {
    // サイドバーを非表示
    var sidebarElements = document.querySelectorAll('#sidebar, .sidebar, #secondary, .widget-area');
    sidebarElements.forEach(function(element) {
        element.style.display = 'none';
    });
    
    // メインコンテンツを100%幅に
    var mainElements = document.querySelectorAll('#main, .main, #primary, .content-area, .container');
    mainElements.forEach(function(element) {
        element.style.width = '100%';
        element.style.maxWidth = '100%';
        element.style.float = 'none';
    });
});
</script>

<div class="job-listing-wrapper">
    <div class="job-listing-container">
        <div class="job-search-header">
            <h1 class="page-title">
                <?php if (!empty($conditions)): ?>
                    <?php echo implode(' × ', $conditions); ?>の求人情報
                <?php else: ?>
                    求人情報一覧
                <?php endif; ?>
            </h1>
            <div class="job-count">
                <p>検索結果: <span class="count-number"><?php echo esc_html($wp_query->found_posts); ?></span>件</p>
            </div>
            
            <!-- 現在の検索条件タグを表示 -->
            <?php if (!empty($conditions)): ?>
            <div class="current-filters">
                <div class="filter-tags">
                    <?php
                    // エリア
                    if (!empty($location_slug)) {
                        $location_term = get_term_by('slug', $location_slug, 'job_location');
                        if ($location_term) {
                            $remove_url = remove_filter_from_url('location');
                            echo '<div class="filter-tag">';
                            echo '<span class="filter-label">エリア:</span> ';
                            echo esc_html($location_term->name);
                            echo '<a href="' . esc_url($remove_url) . '" class="remove-filter">&times;</a>';
                            echo '</div>';
                        }
                    }
                    
                    // 職種
                    if (!empty($position_slug)) {
                        $position_term = get_term_by('slug', $position_slug, 'job_position');
                        if ($position_term) {
                            $remove_url = remove_filter_from_url('position');
                            echo '<div class="filter-tag">';
                            echo '<span class="filter-label">職種:</span> ';
                            echo esc_html($position_term->name);
                            echo '<a href="' . esc_url($remove_url) . '" class="remove-filter">&times;</a>';
                            echo '</div>';
                        }
                    }
                    
                    // 雇用形態
                    if (!empty($job_type_slug)) {
                        $job_type_term = get_term_by('slug', $job_type_slug, 'job_type');
                        if ($job_type_term) {
                            $remove_url = remove_filter_from_url('type');
                            echo '<div class="filter-tag">';
                            echo '<span class="filter-label">雇用形態:</span> ';
                            echo esc_html($job_type_term->name);
                            echo '<a href="' . esc_url($remove_url) . '" class="remove-filter">&times;</a>';
                            echo '</div>';
                        }
                    }
                    
                    // 施設形態
                    if (!empty($facility_type_slug)) {
                        $facility_type_term = get_term_by('slug', $facility_type_slug, 'facility_type');
                        if ($facility_type_term) {
                            $remove_url = remove_filter_from_url('facility');
                            echo '<div class="filter-tag">';
                            echo '<span class="filter-label">施設形態:</span> ';
                            echo esc_html($facility_type_term->name);
                            echo '<a href="' . esc_url($remove_url) . '" class="remove-filter">&times;</a>';
                            echo '</div>';
                        }
                    }
                    
                    // 特徴（単一または複数）
                    if (!empty($feature_slugs)) {
                        foreach ($feature_slugs as $slug) {
                            $feature_term = get_term_by('slug', $slug, 'job_feature');
                            if ($feature_term) {
                                $remove_url = remove_feature_from_url($slug);
                                echo '<div class="filter-tag">';
                                echo '<span class="filter-label">特徴:</span> ';
                                echo esc_html($feature_term->name);
                                echo '<a href="' . esc_url($remove_url) . '" class="remove-filter">&times;</a>';
                                echo '</div>';
                            }
                        }
                    }
                    ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- 検索フォームを表示 -->
            <?php get_template_part('search-form'); ?>
        </div>
        
        <div class="job-list">
            <?php if (have_posts()): ?>
                <?php while (have_posts()): the_post(); 
                    // カスタムフィールドデータの取得
                    $facility_name = get_post_meta(get_the_ID(), 'facility_name', true);
                    $facility_company = get_post_meta(get_the_ID(), 'facility_company', true);
                    $job_content_title = get_post_meta(get_the_ID(), 'job_content_title', true);
                    $salary_range = get_post_meta(get_the_ID(), 'salary_range', true);
                    $facility_address = get_post_meta(get_the_ID(), 'facility_address', true);
                    
                    // タクソノミーの取得
                    $facility_types = get_the_terms(get_the_ID(), 'facility_type');
                    $job_features = get_the_terms(get_the_ID(), 'job_feature');
                    $job_types = get_the_terms(get_the_ID(), 'job_type');
                    $job_positions = get_the_terms(get_the_ID(), 'job_position');
                    
                    // 施設形態のチェック
                    $has_jidou = false;    // 児童発達支援フラグ
                    $has_houkago = false;  // 放課後等デイサービスフラグ

                    if ($facility_types && !is_wp_error($facility_types)) {
                        foreach ($facility_types as $type) {
                            // 組み合わせタイプのチェック
                            if ($type->slug === 'jidou-houkago') {
                                // 児童発達支援・放課後等デイの場合は両方表示
                                $has_jidou = true;
                                $has_houkago = true;
                            } 
                            // 児童発達支援のみのチェック
                            else if ($type->slug === 'jidou') {
                                $has_jidou = true;
                            } 
                            // 放課後等デイサービスのみのチェック
                            else if ($type->slug === 'houkago') {
                                $has_houkago = true;
                            }
                            
                            // 従来の拡張スラッグもサポート（必要に応じて）
                            else if (in_array($type->slug, ['jidou-hattatsu', 'jidou-hattatsu-shien', 'child-development-support'])) {
                                $has_jidou = true;
                            }
                            else if (in_array($type->slug, ['houkago-day', 'houkago-dayservice', 'after-school-day-service'])) {
                                $has_houkago = true;
                            }
                        }
                    }
                    
                    // 雇用形態に基づくカラークラスを設定
                    $employment_color_class = '';
                    if ($job_types && !is_wp_error($job_types)) {
                        switch($job_types[0]->slug) {
                            case 'full-time':
                            case 'seishain': // 正社員
                                $employment_color_class = 'full-time-color';
                                break;
                            case 'part-time':
                            case 'part':
                            case 'arubaito': // パート・アルバイト
                                $employment_color_class = 'part-time-color';
                                break;
                            case 'contract':
                            case 'keiyaku': // 契約社員
                                $employment_color_class = 'contract-color';
                                break;
                            case 'temporary':
                            case 'haken': // 派遣社員
                                $employment_color_class = 'temporary-color';
                                break;
                            default:
                                $employment_color_class = '';
                        }
                    }
                ?>
                
                <div class="job-card">
                    <!-- 上部コンテンツ：左右に分割 -->
                    <div class="job-content">
                        <!-- 左側：サムネイル画像、施設形態アイコン、特徴タグ -->
                        <div class="left-content">
                            <!-- サムネイル画像 -->
                            <div class="job-image">
                                <?php if (has_post_thumbnail()): ?>
                                    <?php the_post_thumbnail('medium'); ?>
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/300x200" alt="<?php echo esc_attr($facility_name); ?>">
                                <?php endif; ?>
                            </div>
                            
                            <!-- 施設形態を画像アイコン -->
                            <div class="facility-icons">
                                <?php if ($has_houkago): ?>
                                <!-- 放デイアイコン -->
                                <div class="facility-icon">
                                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/day.png" alt="放デイ">
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($has_jidou): ?>
                                <!-- 児発支援アイコン -->
                                <div class="facility-icon red-icon">
                                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/support.png" alt="児発支援">
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- 特徴タクソノミータグ - 3つまで表示 -->
                            <?php if ($job_features && !is_wp_error($job_features)): ?>
                            <div class="tags-container">
                                <?php 
                                $features_count = 0;
                                foreach ($job_features as $feature):
                                    if ($features_count < 3):
                                        // プレミアム特徴の判定（例：高収入求人など）
                                        $premium_class = (in_array($feature->slug, ['high-salary', 'bonus-available'])) ? 'premium' : '';
                                ?>
                                    <span class="tag <?php echo $premium_class; ?>"><?php echo esc_html($feature->name); ?></span>
                                <?php
                                        $features_count++;
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- 右側：運営会社名、施設名、本文詳細 -->
                        <div class="right-content">
                            <!-- 会社名と雇用形態を横に並べる -->
                            <div class="company-section">
                                <span class="company-name"><?php echo esc_html($facility_company); ?></span>
                                <?php if ($job_types && !is_wp_error($job_types)): ?>
                                <div class="employment-type <?php echo $employment_color_class; ?>">
                                    <?php echo esc_html($job_types[0]->name); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- 施設名を会社名の下に配置 -->
                            <h1 class="job-title"><?php echo esc_html($facility_name); ?></h1>
                            
                            <h2 class="job-subtitle"><?php echo esc_html($job_content_title); ?></h2>
                            
                            <p class="job-description">
                                <?php echo wp_trim_words(get_the_content(), 40, '...'); ?>
                            </p>
                            
                            <!-- 本文の下に区切り線を追加 -->
                            <div class="divider"></div>
                            
                            <!-- 職種、給料、住所情報 -->
                            <div class="job-info">
                                <?php if ($job_positions && !is_wp_error($job_positions)): ?>
                                <div class="info-item">
                                    <span class="info-icon"><i class="fa-solid fa-user"></i></span>
                                    <span><?php echo esc_html($job_positions[0]->name); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="info-item">
                                    <span class="info-icon"><i class="fa-solid fa-money-bill-wave"></i></span>
                                    <span><?php echo esc_html($salary_range); ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-icon"><i class="fa-solid fa-location-dot"></i></span>
                                    <span><?php echo esc_html($facility_address); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 区切り線 -->
                    <div class="divider"></div>
                    
                    <!-- ボタンエリア -->
                    <div class="buttons-container">
                        <?php if (is_user_logged_in()): 
                            // お気に入り状態の確認
                            $user_id = get_current_user_id();
                            $favorites = get_user_meta($user_id, 'job_favorites', true);
                            $is_favorite = is_array($favorites) && in_array(get_the_ID(), $favorites);
                        ?>
                            <button class="keep-button <?php echo $is_favorite ? 'kept' : ''; ?>" data-job-id="<?php echo get_the_ID(); ?>">
                                <span class="star"></span>
                                <?php echo $is_favorite ? 'キープ済み' : 'キープ'; ?>
                            </button>
                        <?php else: ?>
                            <a href="<?php echo wp_login_url(get_permalink()); ?>" class="keep-button">
                                <span class="star"></span>キープ
                            </a>
                        <?php endif; ?>
                        
                        <a href="<?php the_permalink(); ?>" class="detail-view-button">詳細をみる</a>
                    </div>
                </div>
                <?php endwhile; ?>
                
                <!-- ページネーション -->
                <div class="pagination">
                    <?php
                    echo paginate_links(array(
                        'prev_text' => '&laquo; 前へ',
                        'next_text' => '次へ &raquo;',
                    ));
                    ?>
                </div>
                
            <?php else: ?>
                <div class="no-jobs-found">
                    <p>条件に一致する求人が見つかりませんでした。検索条件を変更して再度お試しください。</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- キープボタン用JavaScriptコード -->
<script>
jQuery(document).ready(function($) {
    // キープボタン機能
    $('.keep-button').on('click', function() {
        // リンクでない場合のみ処理（ログイン済みユーザー用）
        if (!$(this).attr('href')) {
            var jobId = $(this).data('job-id');
            var $button = $(this);
            
            // AJAXでキープ状態を切り替え
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'toggle_job_favorite',
                    job_id: jobId,
                    nonce: '<?php echo wp_create_nonce('job_favorite_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        if (response.data.status === 'added') {
                            $button.addClass('kept');
                            $button.html('<span class="star"><i class="fa-solid fa-star"></i></span> キープ済み');
                        } else {
                            $button.removeClass('kept');
                            $button.html('<span class="star"><i class="fa-solid fa-star"></i></span> キープ');
                        }
                    }
                }
            });
        }
    });
});
</script>

<style>
/* 求人カードのスタイル */
.job-card {
    background-color: white;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 1000px;
	overflow: hidden;
    padding: 20px;
    margin-bottom: 30px;
}

.job-content {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

/* 左側のスタイル */
.left-content {
    width: 30%;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.job-image {
    width: 100%;
    border-radius: 8px;
    overflow: hidden;
}

.job-image img {
    width: 100%;
    height: auto;
    object-fit: cover;
}

.tags-container {
    display: flex;
    flex-wrap: nowrap;
    gap: 5px;
    justify-content: flex-start;
    width: 100%;
}

.tag {
    background-color: #fff;
    border: 1px solid #FFB74D;
    color: #FF9800;
    padding: 3px 5px;
    border-radius: 20px;
    font-size: 10px;
    white-space: nowrap;
    flex: 1;
    text-align: center;
}

.tag.premium {
    background-color: #fff;
    border: 1px solid #FFA000;
    color: #FFA000;
}

/* 右側のスタイル */
.right-content {
    width: 70%;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.company-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
}

.company-name {
    color: #666;
    font-size: 14px;
    text-align: left;
    margin-left: 0;
    padding-left: 0;
}

.job-title {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 10px;
}

.job-subtitle {
    font-size: 16px;
    margin-bottom: 10px;
}

.job-description {
    font-size: 14px;
    color: #333;
    line-height: 1.6;
}

/* 区切り線 */
.divider {
    height: 1px;
    background-color: #eee;
    margin: 15px 0;
}

.job-info {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 15px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-icon {
    width: 20px;
    color: #999;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ボタンエリア */
.buttons-container {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}

.keep-button {
    background-color: #fff;
    border: 1px solid #FFB74D;
    color: #FF9800;
    padding: 15px 20px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    width: 45%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    text-decoration: none;
}

.keep-button .star {
    color: #FFB74D;
    margin-right: 10px;
}

.keep-button.kept {
    background-color: #FFF8E1;
}

.detail-view-button {
    background-color: #26A69A;
    border: none;
    color: white;
    padding: 15px 20px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    width: 45%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    text-decoration: none;
}

/* レスポンシブ対応 */
@media (max-width: 768px) {
    .job-content {
        flex-direction: column;
    }
    
    .left-content, .right-content {
        width: 100%;
    }
    
    .buttons-container {
        flex-direction: column;
        gap: 10px;
    }
    
    .keep-button, .detail-view-button {
        width: 100%;
    }
}
</style>

<?php get_footer(); ?>