require('./bootstrap');

document.querySelector('.image-picker input')
    .addEventListener('change',(e) => {
        const input = e.target;
        const reader = new FileReader();
        reader.onload = (e) => {
            // inputタグから親方向であるimageクラスのDOMを探しそこから更にimgタグのDOMを検索
            // e.target.resultは画像データをbase64エンコードしている
            input.closest('.image-picker').querySelector('img').src = e.target.result
        };
        reader.readAsDataURL(input.files[0]);
    });