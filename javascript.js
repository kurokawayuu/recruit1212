//ここに追加したいJavaScript、jQueryを記入してください。
//このJavaScriptファイルは、親テーマのJavaScriptファイルのあとに呼び出されます。
//JavaScriptやjQueryで親テーマのjavascript.jsに加えて関数を記入したい時に使用します。

/**
 * スライダー機能
 */
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.querySelector('.slider');
    const slides = document.querySelectorAll('.slide');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    const dots = document.querySelectorAll('.dot');
    
    let currentSlide = 0;
    const totalSlides = slides.length;
    
    // スライドを表示する関数
    function showSlide(n) {
        currentSlide = (n + totalSlides) % totalSlides;
        slider.style.transform = `translateX(-${currentSlide * 100}%)`;
        
        // アクティブなドットを更新
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === currentSlide);
        });
    }
    
    // 次のスライドへ
    function nextSlide() {
        showSlide(currentSlide + 1);
    }
    
    // 前のスライドへ
    function prevSlide() {
        showSlide(currentSlide - 1);
    }
    
    // イベントリスナーを追加
    prevBtn.addEventListener('click', prevSlide);
    nextBtn.addEventListener('click', nextSlide);
    
    // ドットクリックでスライド切り替え
    dots.forEach((dot, i) => {
        dot.addEventListener('click', () => showSlide(i));
    });
    
    // 自動スライド（5秒ごと）
    const autoSlideInterval = setInterval(nextSlide, 5000);
    
    // スライダーにマウスが乗ったら自動スライドを停止
    slider.addEventListener('mouseenter', () => {
        clearInterval(autoSlideInterval);
    });
    
    // スライダーからマウスが離れたら自動スライドを再開
    slider.addEventListener('mouseleave', () => {
        autoSlideInterval = setInterval(nextSlide, 5000);
    });
    
    // 初期表示
    showSlide(0);
});

/**
 * 求人検索フォーム用のJavaScript
 */
jQuery(document).ready(function($) {
    console.log('求人検索スクリプトを読み込みました'); // デバッグ用
    
    // グローバル変数を定義
    var ajaxurl = job_search_params.ajax_url;
    var site_url = job_search_params.site_url;
    
    // 現在の日付を設定
    var today = new Date();
    var year = today.getFullYear();
    var month = today.getMonth() + 1;
    var day = today.getDate();
    $('#update-date').text(year + '年' + month + '月' + day + '日');
    
    // 詳細検索の表示/非表示切り替え
    $('#detail-toggle-btn').on('click', function() {
        var $detailSection = $('.detail-search-section');
        if ($detailSection.is(':visible')) {
            $detailSection.slideUp();
            $(this).text('詳細を指定');
        } else {
            $detailSection.slideDown();
            $(this).text('詳細条件を閉じる');
        }
    });
    
    // 選択フィールドをクリックしたときの処理
    $('#area-field').on('click', function() {
        console.log('エリアフィールドがクリックされました'); // デバッグ用
        openModal('area-modal-overlay');
        // 最初のステップを表示
        $('#area-selection-modal').show();
        $('#prefecture-selection-modal').hide();
        $('#city-selection-modal').hide();
    });
    
    $('#position-field').on('click', function() {
        console.log('職種フィールドがクリックされました'); // デバッグ用
        openModal('position-modal-overlay');
    });
    
    $('#job-type-field').on('click', function() {
        console.log('雇用形態フィールドがクリックされました'); // デバッグ用
        openModal('job-type-modal-overlay');
    });
    
    $('#facility-type-field').on('click', function() {
        console.log('施設形態フィールドがクリックされました'); // デバッグ用
        openModal('facility-type-modal-overlay');
    });
    
    $('#feature-field').on('click', function() {
        console.log('特徴フィールドがクリックされました'); // デバッグ用
        // チェックボックスの状態を初期化
        resetFeatureCheckboxes();
        openModal('feature-modal-overlay');
    });
    
    // モーダルを開く
    function openModal(modalId) {
        console.log('モーダルを開きます: ' + modalId); // デバッグ用
        // すべてのモーダルを非表示にする
        $('.modal-overlay').removeClass('active');
        
        // 指定されたモーダルのみ表示する
        $('#' + modalId).addClass('active');
    }
    
    // モーダルを閉じる
    $('.modal-close').on('click', function() {
        var target = $(this).data('target');
        $('#' + target).removeClass('active'); // activeクラスを削除
    });
    
    // 背景クリックでモーダルを閉じる
    $('.modal-overlay').on('click', function(e) {
        if ($(e.target).is('.modal-overlay')) {
            $(this).removeClass('active'); // activeクラスを削除
        }
    });
    
    // トップレベルのエリア選択時の処理
    $(document).on('click', '.area-btn', function() {
        var termId = $(this).data('term-id');
        var termName = $(this).data('name');
        var termSlug = $(this).data('slug');
        
        // エリア情報を一時保存
        sessionStorage.setItem('selectedAreaId', termId);
        sessionStorage.setItem('selectedAreaName', termName);
        sessionStorage.setItem('selectedAreaSlug', termSlug);
        
        // 選択したエリア名を表示
        $('#selected-area-name').text(termName);
        $('#selected-area-btn-name').text(termName);
        
        // 第2階層のタームをロード
        loadSecondLevelTerms(termId);
        
        // モーダルを切り替え
        $('#area-selection-modal').hide();
        $('#prefecture-selection-modal').fadeIn(300);
    });
    
    // 「全域で検索」ボタン（第1階層）の処理
    $('#select-area-btn').on('click', function() {
        var areaName = sessionStorage.getItem('selectedAreaName');
        var areaSlug = sessionStorage.getItem('selectedAreaSlug');
        var areaId = sessionStorage.getItem('selectedAreaId');
        
        // URLを構築するために使用するTermオブジェクトを取得
        var termUrl = getTermUrl('job_location', areaId);
        
        // 表示テキストを更新
        updateSelectionDisplay('#area-field', areaName);
        
        // hidden inputに値をセット
        $('#location-input').val(areaSlug);
        $('#location-name-input').val(areaName);
        $('#location-term-id-input').val(areaId);
        
        // 第1階層のURLを保存
        sessionStorage.setItem('selectedLocationUrl', termUrl);
        
        // モーダルを閉じる
        $('#area-modal-overlay').removeClass('active');
    });
    
    // 第2階層のターム選択時の処理
    $(document).on('click', '.prefecture-btn', function() {
        var termId = $(this).data('term-id');
        var termName = $(this).data('name');
        var termSlug = $(this).data('slug');
        
        // 都道府県情報を一時保存
        sessionStorage.setItem('selectedPrefectureId', termId);
        sessionStorage.setItem('selectedPrefectureName', termName);
        sessionStorage.setItem('selectedPrefectureSlug', termSlug);
        
        // URLを構築するために使用するTermオブジェクトを取得
        var termUrl = getTermUrl('job_location', termId);
        sessionStorage.setItem('selectedPrefectureUrl', termUrl);
        
        // 選択した都道府県名を表示
        $('#selected-prefecture-name').text(termName);
        $('#selected-prefecture-btn-name').text(termName);
        
        // 第3階層の市区町村タームを取得
        loadThirdLevelTerms(termId);
        
        // モーダルを切り替え
        $('#prefecture-selection-modal').hide();
        $('#city-selection-modal').fadeIn(300);
    });
    
    // 「全域で検索」ボタン（第2階層）の処理
    $('#select-prefecture-btn').on('click', function() {
        var prefectureName = sessionStorage.getItem('selectedPrefectureName');
        var prefectureSlug = sessionStorage.getItem('selectedPrefectureSlug');
        var prefectureId = sessionStorage.getItem('selectedPrefectureId');
        
        // 表示テキストを更新
        updateSelectionDisplay('#area-field', prefectureName);
        
        // hidden inputに値をセット
        $('#location-input').val(prefectureSlug);
        $('#location-name-input').val(prefectureName);
        $('#location-term-id-input').val(prefectureId);
        
        // モーダルを閉じる
        $('#area-modal-overlay').removeClass('active');
    });
    
    // 第3階層のターム選択時の処理
    $(document).on('click', '.city-btn', function() {
        var termId = $(this).data('term-id');
        var termName = $(this).data('name');
        var termSlug = $(this).data('slug');
        var prefectureName = sessionStorage.getItem('selectedPrefectureName');
        
        // URLを構築するために使用するTermオブジェクトを取得
        var termUrl = getTermUrl('job_location', termId);
        
        // 表示テキストを更新
        var displayText = prefectureName + ' ' + termName;
        updateSelectionDisplay('#area-field', displayText);
        
        // hidden inputに値をセット
        $('#location-input').val(termSlug);
        $('#location-name-input').val(displayText);
        $('#location-term-id-input').val(termId);
        
        // 市区町村のURLを保存
        sessionStorage.setItem('selectedLocationUrl', termUrl);
        
        // モーダルを閉じる
        $('#area-modal-overlay').removeClass('active');
    });
    
    // 職種選択時の処理
    $(document).on('click', '.position-btn', function() {
        var termId = $(this).data('term-id');
        var termName = $(this).data('name');
        var termSlug = $(this).data('slug');
        var termUrl = $(this).data('url');
        
        // 表示テキストを更新
        updateSelectionDisplay('#position-field', termName);
        
        // hidden inputに値をセット
        $('#position-input').val(termSlug);
        $('#position-name-input').val(termName);
        $('#position-term-id-input').val(termId);
        
        // URLを一時保存
        sessionStorage.setItem('selectedPositionUrl', termUrl);
        
        // モーダルを閉じる
        $('#position-modal-overlay').removeClass('active');
    });
    
    // 雇用形態選択時の処理
    $(document).on('click', '.job-type-btn', function() {
        var termId = $(this).data('term-id');
        var termName = $(this).data('name');
        var termSlug = $(this).data('slug');
        var termUrl = $(this).data('url');
        
        // 表示テキストを更新
        updateSelectionDisplay('#job-type-field', termName);
        
        // hidden inputに値をセット
        $('#job-type-input').val(termSlug);
        $('#job-type-name-input').val(termName);
        $('#job-type-term-id-input').val(termId);
        
        // URLを一時保存
        sessionStorage.setItem('selectedJobTypeUrl', termUrl);
        
        // モーダルを閉じる
        $('#job-type-modal-overlay').removeClass('active');
    });
    
    // 施設形態選択時の処理
    $(document).on('click', '.facility-type-btn', function() {
        var termId = $(this).data('term-id');
        var termName = $(this).data('name');
        var termSlug = $(this).data('slug');
        var termUrl = $(this).data('url');
        
        // 表示テキストを更新
        updateSelectionDisplay('#facility-type-field', termName);
        
        // hidden inputに値をセット
        $('#facility-type-input').val(termSlug);
        $('#facility-type-name-input').val(termName);
        $('#facility-type-term-id-input').val(termId);
        
        // URLを一時保存
        sessionStorage.setItem('selectedFacilityTypeUrl', termUrl);
        
        // モーダルを閉じる
        $('#facility-type-modal-overlay').removeClass('active');
    });
    
    // 特徴の適用ボタンの処理
    $('#apply-features-btn').on('click', function() {
        var selectedFeatures = [];
        var featureSlugs = [];
        var featureIds = [];
        
        // チェックされた特徴を取得
        $('.feature-checkbox:checked').each(function() {
            var termId = $(this).data('term-id');
            var termName = $(this).data('name');
            var termSlug = $(this).data('slug');
            
            selectedFeatures.push({
                id: termId,
                name: termName,
                slug: termSlug
            });
            
            featureSlugs.push(termSlug);
            featureIds.push(termId);
        });
        
        // 選択した特徴を表示
        updateFeatureSelection(selectedFeatures);
        
        // hidden inputに値をセット
        $('#job-feature-input').val(featureSlugs.join(','));
        
        // モーダルを閉じる
        $('#feature-modal-overlay').removeClass('active');
    });
    
    // 戻るボタンの処理
    $('.back-btn').on('click', function() {
        var target = $(this).data('target');
        
        // 現在のモーダルを非表示
        $(this).closest('.modal-panel').hide();
        
        // ターゲットモーダルを表示
        $('#' + target).fadeIn(300);
    });
    
    // 検索ボタンクリック時の処理
    $('#search-btn').on('click', function() {
        console.log('検索ボタンがクリックされました'); // デバッグ用
        var baseUrl = site_url + '/jobs/';
        var filters = [];
        var queryParams = [];
        var hasPathFilters = false;
        
        // エリア
        var locationSlug = $('#location-input').val();
        if (locationSlug) {
            filters.push('location/' + locationSlug);
            hasPathFilters = true;
        }
        
        // 職種
        var positionSlug = $('#position-input').val();
        if (positionSlug) {
            filters.push('position/' + positionSlug);
            hasPathFilters = true;
        }
        
        // 詳細条件が表示されている場合
        if ($('.detail-search-section').is(':visible')) {
            // 雇用形態
            var jobTypeSlug = $('#job-type-input').val();
            if (jobTypeSlug) {
                filters.push('type/' + jobTypeSlug);
                hasPathFilters = true;
            }
            
            // 施設形態
            var facilityTypeSlug = $('#facility-type-input').val();
            if (facilityTypeSlug) {
                filters.push('facility/' + facilityTypeSlug);
                hasPathFilters = true;
            }
            
            // 特徴（複数選択をクエリパラメータとして扱う）
            var featureSlugStr = $('#job-feature-input').val();
            if (featureSlugStr) {
                var featureSlugs = featureSlugStr.split(',');
                if (featureSlugs.length === 1) {
                    // 単一の特徴はURLパスに組み込む
                    filters.push('feature/' + featureSlugs[0]);
                    hasPathFilters = true;
                } else if (featureSlugs.length > 1) {
                    // 複数の特徴はクエリパラメータとして処理
                    for (var i = 0; i < featureSlugs.length; i++) {
                        queryParams.push('features[]=' + featureSlugs[i]);
                    }
                }
            }
        }
        
        // 選択条件がない場合
        if (!hasPathFilters && queryParams.length === 0) {
            alert('検索条件を1つ以上選択してください');
            return;
        }
        
        // URLの構築
        var targetUrl;
        
        if (hasPathFilters) {
            // 主要条件がある場合は通常のパスベースURL
            targetUrl = baseUrl + filters.join('/') + '/';
        } else {
            // 特徴のみの場合は専用のエンドポイント
            targetUrl = baseUrl + 'features/';
        }
        
        // クエリパラメータを追加
        if (queryParams.length > 0) {
            targetUrl += '?' + queryParams.join('&');
        }
        
        console.log('生成されたURL:', targetUrl);
        
        // 検索結果ページに遷移
        window.location.href = targetUrl;
    });
    
    // 選択表示の更新
    function updateSelectionDisplay(fieldSelector, text) {
        var $field = $(fieldSelector);
        $field.find('.selection-display').text(text);
        $field.find('.selection-display').removeClass('selection-placeholder');
    }
    
    // 特徴選択の表示を更新
    function updateFeatureSelection(features) {
        var $selectedFeatures = $('#selected-features');
        var $featureField = $('#feature-field');
        
        if (features.length === 0) {
            $featureField.find('.feature-selection-display').text('特徴を選択（複数選択可）');
            $featureField.find('.feature-selection-display').addClass('feature-placeholder');
            $selectedFeatures.empty();
            return;
        }
        
        $featureField.find('.feature-selection-display').text('選択済み：' + features.length + '件');
        $featureField.find('.feature-selection-display').removeClass('feature-placeholder');
        
        $selectedFeatures.empty();
        for (var i = 0; i < features.length; i++) {
            var feature = features[i];
            var $tag = $('<div class="feature-tag">' + feature.name + '</div>');
            $selectedFeatures.append($tag);
        }
    }
    
    // 特徴チェックボックスのリセット
    function resetFeatureCheckboxes() {
        $('.feature-checkbox').prop('checked', false);
        
        // 現在選択されている特徴に基づいてチェックを復元
        var selectedFeatureSlugs = $('#job-feature-input').val();
        if (selectedFeatureSlugs) {
            var slugs = selectedFeatureSlugs.split(',');
            for (var i = 0; i < slugs.length; i++) {
                $('.feature-checkbox[data-slug="' + slugs[i] + '"]').prop('checked', true);
            }
        }
    }
    
    // 第2階層のタームをロードする関数
    function loadSecondLevelTerms(parentId) {
        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'get_taxonomy_children',
                parent_id: parentId,
                taxonomy: 'job_location',
                nonce: job_search_params.nonce
            },
            success: function(response) {
                if (response.success) {
                    displaySecondLevelTerms(response.data);
                } else {
                    $('#prefecture-grid').html('<p>階層が見つかりませんでした</p>');
                }
            },
            error: function() {
                $('#prefecture-grid').html('<p>エラーが発生しました</p>');
            }
        });
    }
    
    // 第2階層のタームを表示する関数
    function displaySecondLevelTerms(terms) {
        var $grid = $('#prefecture-grid');
        $grid.empty();
        
        if (terms.length === 0) {
            $grid.html('<p>該当するエリアがありません</p>');
            return;
        }
        
        for (var i = 0; i < terms.length; i++) {
            var term = terms[i];
            var $btn = $('<div class="prefecture-btn" data-term-id="' + term.term_id + '" data-name="' + term.name + '" data-slug="' + term.slug + '">' + term.name + '</div>');
            $grid.append($btn);
        }
    }
    
    // 第3階層のタームをロードする関数
    function loadThirdLevelTerms(parentId) {
        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'get_taxonomy_children',
                parent_id: parentId,
                taxonomy: 'job_location',
                nonce: job_search_params.nonce
            },
            success: function(response) {
                if (response.success) {
                    displayThirdLevelTerms(response.data);
                } else {
                    $('#city-grid').html('<p>市区町村が見つかりませんでした</p>');
                }
            },
            error: function() {
                $('#city-grid').html('<p>エラーが発生しました</p>');
            }
        });
    }
    
    // 第3階層のタームを表示する関数
    function displayThirdLevelTerms(terms) {
        var $grid = $('#city-grid');
        $grid.empty();
        
        if (terms.length === 0) {
            $grid.html('<p>該当する市区町村がありません</p>');
            return;
        }
        
        for (var i = 0; i < terms.length; i++) {
            var term = terms[i];
            var $btn = $('<div class="city-btn" data-term-id="' + term.term_id + '" data-name="' + term.name + '" data-slug="' + term.slug + '">' + term.name + '</div>');
            $grid.append($btn);
        }
    }
    
    // タクソノミーのURLを取得する関数
    function getTermUrl(taxonomy, termId) {
        var url = '';
        
        $.ajax({
            url: ajaxurl,
            type: 'post',
            async: false, // 同期リクエスト
            data: {
                action: 'get_term_link',
                term_id: termId,
                taxonomy: taxonomy,
                nonce: job_search_params.nonce
            },
            success: function(response) {
                if (response.success) {
                    url = response.data;
                }
            }
        });
        
        return url;
    }
});




jQuery(document).ready(function($) {
    // 求人カードスライダー機能
    var jobContainer = $('.job-container');
    var jobCards = $('.job-card');
    var nextBtn = $('#next-job-button');
    var prevBtn = $('#prev-job-button');
    var indicators = $('.indicator');
    
    // 変数の初期化
    var cardWidth = jobCards.first().outerWidth(true);
    var containerWidth = $('.job-slider-wrapper').width();
    var cardsPerView = Math.floor(containerWidth / cardWidth);
    var totalCards = jobCards.length;
    var totalSlides = Math.ceil(totalCards / cardsPerView);
    var currentSlide = 0;
    
    // スライダーの更新
    function updateSlider() {
        // コンテナ幅とカード表示数の再計算
        containerWidth = $('.job-slider-wrapper').width();
        cardWidth = jobCards.first().outerWidth(true);
        cardsPerView = Math.max(1, Math.floor(containerWidth / cardWidth));
        
        // 総スライド数の再計算
        totalSlides = Math.ceil(totalCards / cardsPerView);
        
        // 現在のスライドを有効範囲内に制限
        currentSlide = Math.min(currentSlide, totalSlides - 1);
        
        // スライド位置の計算
        var translateX = -currentSlide * cardsPerView * cardWidth;
        
        // スライド位置を制約内に収める
        var maxTranslate = -(totalCards * cardWidth - containerWidth);
        translateX = Math.max(translateX, maxTranslate);
        translateX = Math.min(translateX, 0);
        
        // カードコンテナの位置を更新
        jobContainer.css('transform', 'translateX(' + translateX + 'px)');
        
        // インジケーターの更新
        $('.indicator').removeClass('active');
        $('.indicator[data-slide="' + currentSlide + '"]').addClass('active');
        
        // ナビゲーションボタンの有効/無効状態を更新
        updateNavButtons();
    }
    
    // ナビゲーションボタンの表示状態を更新
    function updateNavButtons() {
        // 前へボタンの表示/非表示
        if (currentSlide <= 0) {
            prevBtn.parent().addClass('disabled');
        } else {
            prevBtn.parent().removeClass('disabled');
        }
        
        // 次へボタンの表示/非表示
        if (currentSlide >= totalSlides - 1 || totalCards <= cardsPerView) {
            nextBtn.parent().addClass('disabled');
        } else {
            nextBtn.parent().removeClass('disabled');
        }
        
        // スライドが1つしかない場合、両方のボタンを非表示
        if (totalSlides <= 1 || totalCards <= cardsPerView) {
            nextBtn.parent().addClass('disabled');
            prevBtn.parent().addClass('disabled');
        }
        
        // インジケーターの表示/非表示
        if (totalSlides <= 1 || totalCards <= cardsPerView) {
            $('.slide-indicators').hide();
        } else {
            $('.slide-indicators').show();
        }
    }
    
    // 次へボタンのクリックイベント
    nextBtn.on('click', function() {
        if (currentSlide < totalSlides - 1) {
            currentSlide++;
            updateSlider();
        }
    });
    
    // 前へボタンのクリックイベント
    prevBtn.on('click', function() {
        if (currentSlide > 0) {
            currentSlide--;
            updateSlider();
        }
    });
    
    // インジケーターのクリックイベント
    $(document).on('click', '.indicator', function() {
        currentSlide = $(this).data('slide');
        updateSlider();
    });
    
    // ウィンドウリサイズ時の処理
    $(window).resize(function() {
        // リサイズ中に何度も実行されないようにタイマーを設定
        clearTimeout(window.resizedFinished);
        window.resizedFinished = setTimeout(function() {
            updateSlider();
        }, 250);
    });
    
    // スワイプ対応（タッチデバイス用）
    var touchStartX = 0;
    var touchEndX = 0;
    
    jobContainer.on('touchstart', function(e) {
        touchStartX = e.originalEvent.touches[0].clientX;
    });
    
    jobContainer.on('touchend', function(e) {
        touchEndX = e.originalEvent.changedTouches[0].clientX;
        handleSwipe();
    });
    
    function handleSwipe() {
        // 50px以上のスワイプを検出
        if (touchStartX - touchEndX > 50) {
            // 左スワイプ（次へ）
            if (currentSlide < totalSlides - 1) {
                currentSlide++;
                updateSlider();
            }
        } else if (touchEndX - touchStartX > 50) {
            // 右スワイプ（前へ）
            if (currentSlide > 0) {
                currentSlide--;
                updateSlider();
            }
        }
    }
    
    // 初期化
    updateSlider();
});

