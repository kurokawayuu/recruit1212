<?php
/**
 * Template Name: 募集求人一覧ページ
 * 
 * ログインユーザーが投稿した求人を一覧表示するテンプレート
 */

get_header();

// ログインチェック
if (!is_user_logged_in()) {
    // 非ログインの場合はログインページにリダイレクト
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

// 現在のユーザー情報を取得
$current_user = wp_get_current_user();
$current_user_id = $current_user->ID;

// ユーザーが加盟教室（agency）の権限を持っているかチェック
$is_agency = in_array('agency', $current_user->roles);
if (!$is_agency && !current_user_can('administrator')) {
    // 権限がない場合はエラーメッセージ表示
    echo '<div class="error-message">この機能を利用する権限がありません。</div>';
    get_footer();
    exit;
}
?>

<div class="job-list-container">
    <h1 class="page-title">求人情報管理</h1>
    
    <div class="job-action-buttons">
        <a href="<?php echo home_url('/post-job/'); ?>" class="btn-new-job">新しい求人を投稿</a>
    </div>
    
    <?php
    // 求人投稿の取得
    $args = array(
        'post_type' => 'job',
        'posts_per_page' => -1,
        'author' => $current_user_id,
        'post_status' => array('publish', 'draft', 'pending')
    );
    
    // 管理者の場合は全ての投稿を表示
    if (current_user_can('administrator')) {
        unset($args['author']);
    }
    
    $job_query = new WP_Query($args);
    
    if ($job_query->have_posts()) :
    ?>
    
    <div class="job-list">
        <div class="job-list-header">
            <div class="job-header-item job-title-header">求人タイトル</div>
            <div class="job-header-item job-status-header">ステータス</div>
            <div class="job-header-item job-date-header">投稿日</div>
            <div class="job-header-item job-actions-header">操作</div>
        </div>
        
        <?php while ($job_query->have_posts()) : $job_query->the_post(); ?>
        
        <div class="job-list-item">
            <div class="job-item-cell job-title-cell">
                <a href="<?php the_permalink(); ?>" class="job-title-link"><?php the_title(); ?></a>
                
                <?php
                // タクソノミー情報を表示
                $job_types = get_the_terms(get_the_ID(), 'job_type');
                if ($job_types && !is_wp_error($job_types)) {
                    echo '<div class="job-taxonomy-info">';
                    foreach ($job_types as $type) {
                        echo '<span class="job-type-tag">' . $type->name . '</span>';
                    }
                    echo '</div>';
                }
                ?>
            </div>
            
            <div class="job-item-cell job-status-cell">
                <?php
                // 投稿ステータスの表示
                $status = get_post_status();
                $status_label = '';
                
                switch ($status) {
                    case 'publish':
                        $status_label = '<span class="status-publish">公開中</span>';
                        break;
                    case 'draft':
                        $status_label = '<span class="status-draft">下書き</span>';
                        break;
                    case 'pending':
                        $status_label = '<span class="status-pending">承認待ち</span>';
                        break;
                    default:
                        $status_label = '<span class="status-other">' . $status . '</span>';
                }
                
                echo $status_label;
                ?>
            </div>
            
            <div class="job-item-cell job-date-cell">
                <?php echo get_the_date('Y年m月d日'); ?>
            </div>
            
            <div class="job-item-cell job-actions-cell">
                <a href="<?php echo home_url('/edit-job/?job_id=' . get_the_ID()); ?>" class="btn-edit">編集</a>
                
                <?php if (get_post_status() == 'publish') : ?>
                <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=draft_job&job_id=' . get_the_ID()), 'draft_job_' . get_the_ID()); ?>" class="btn-draft">下書きにする</a>
                <?php else : ?>
                <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=publish_job&job_id=' . get_the_ID()), 'publish_job_' . get_the_ID()); ?>" class="btn-publish">公開する</a>
                <?php endif; ?>
                
                <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=delete_job&job_id=' . get_the_ID()), 'delete_job_' . get_the_ID()); ?>" class="btn-delete" onclick="return confirm('本当にこの求人を削除しますか？この操作は元に戻せません。');">削除</a>
            </div>
        </div>
        
        <?php endwhile; ?>
    </div>
    
    <?php
    else :
        // 求人がない場合
        echo '<div class="no-jobs-message">';
        echo '<p>投稿した求人情報はありません。</p>';
        echo '<p><a href="' . home_url('/post-job/') . '" class="btn-new-job">最初の求人を投稿する</a></p>';
        echo '</div>';
    endif;
    
    wp_reset_postdata();
    ?>
</div>

<style>
/* 求人一覧ページのスタイル */
.job-list-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.page-title {
    font-size: 24px;
    margin-bottom: 20px;
}

.job-action-buttons {
    margin-bottom: 20px;
}

.btn-new-job {
    display: inline-block;
    padding: 10px 15px;
    background-color: #ff9800;
    color: #fff;
    text-decoration: none;
    border-radius: 4px;
    font-weight: bold;
}

.job-list {
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
}

.job-list-header {
    display: flex;
    background-color: #f5f5f5;
    font-weight: bold;
    border-bottom: 2px solid #e0e0e0;
}

.job-header-item {
    padding: 12px 15px;
}

.job-title-header {
    flex: 3;
}

.job-status-header, 
.job-date-header {
    flex: 1;
}

.job-actions-header {
    flex: 2;
    text-align: center;
}

.job-list-item {
    display: flex;
    border-bottom: 1px solid #e0e0e0;
}

.job-list-item:last-child {
    border-bottom: none;
}

.job-item-cell {
    padding: 15px;
}

.job-title-cell {
    flex: 3;
}

.job-status-cell, 
.job-date-cell {
    flex: 1;
    display: flex;
    align-items: center;
}

.job-actions-cell {
    flex: 2;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.job-title-link {
    font-weight: bold;
    color: #333;
    text-decoration: none;
}

.job-title-link:hover {
    text-decoration: underline;
}

.job-taxonomy-info {
    margin-top: 5px;
}

.job-type-tag {
    display: inline-block;
    padding: 3px 8px;
    background-color: #e0f7fa;
    color: #0097a7;
    border-radius: 3px;
    font-size: 12px;
    margin-right: 5px;
}

.status-publish {
    color: #4caf50;
    font-weight: bold;
}

.status-draft {
    color: #9e9e9e;
}

.status-pending {
    color: #ff9800;
}

.btn-edit, 
.btn-draft, 
.btn-publish, 
.btn-delete {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 3px;
    font-size: 13px;
    text-decoration: none;
    text-align: center;
}

.btn-edit {
    background-color: #2196f3;
    color: white;
}

.btn-draft {
    background-color: #9e9e9e;
    color: white;
}

.btn-publish {
    background-color: #4caf50;
    color: white;
}

.btn-delete {
    background-color: #f44336;
    color: white;
}

.no-jobs-message {
    text-align: center;
    padding: 40px 20px;
    background-color: #f5f5f5;
    border-radius: 4px;
}

.error-message {
    background-color: #ffebee;
    color: #c62828;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

/* レスポンシブ対応 */
@media (max-width: 768px) {
    .job-list-header, 
    .job-list-item {
        flex-direction: column;
    }
    
    .job-header-item, 
    .job-item-cell {
        width: 100%;
        box-sizing: border-box;
    }
    
    .job-actions-cell {
        display: flex;
        justify-content: space-between;
    }
}
</style>

<?php get_footer(); ?>