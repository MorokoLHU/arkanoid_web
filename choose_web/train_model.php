
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 取得使用者選擇的模型
    $model = $_POST["model"];
    
    $task_type = $_POST["task_type"];
    // 設定 Python 檔案路徑
    $python_scripts = [
        "DecisionTree" => [
            "classification" => "../model_train_Decisiontree_classification.py",
            "regression" => "../model_train_Decisiontree_regression.py"
        ],
        "KNeighbors" => [
            "classification" => "../model_train_KNN_classification.py",
            "regression" => "../model_train_KNN_regression.py"
        ],
        "RandomForest" => [
            "classification" => "../model_train_RandomForest_classification.py",
            "regression" => "../model_train_RandomForest_regression.py"
        ],
        "LinearSVC" => [
            "classification" => "../model_train_linearSVC_classification.py",
            "regression" => null // LinearSVC 通常不適用於 Regression
        ],
        "LinearSVR" => [
            "classification" => null, // LinearSVR 是用於 Regression，不適用於 Classification
            "regression" => "../model_train_linearSVR_regression.py"
        ],
        "LogisticRegression" => [
            "classification" => "../model_train_LogisticRegression_classification.py",
            "regression" => null // Logistic Regression 主要用於分類
        ],
        "LinearRegression" => [
            "classification" => null, // Linear Regression 只適用於 Regression
            "regression" => "../model_train_Linear_regression.py"
        ],
        "SVM" => [
            "classification" => "../model_train_SVM_classification.py",
            "regression" => "../model_train_SVM_regression.py"
        ]
    ];
    $script_path = $python_scripts[$model][$task_type];

    if (isset($python_scripts[$model][$task_type])) {
        $script_name = $python_scripts[$model][$task_type]; // 取得對應的 Python 腳本
        echo "即將執行的 Python 腳本：" . $script_name . "<br>";
        
        // 確保腳本存在
        if (!file_exists($script_path)) {
            die("<h3>錯誤：找不到 Python 腳本！</h3>");
        }
        // 初始化參數變數
        $params = [];
    
        // 根據選擇的模型，獲取額外的參數
        if ($model == "KNeighbors") {
            $k_value = $_POST["k_value"];
            $params[] = escapeshellarg($k_value);
        } elseif ($model == "DecisionTree") {
            $max_depth = $_POST["max_depth"];
            $params[] = escapeshellarg($max_depth);
        } elseif ($model == "RandomForest") {
            $max_depth = $_POST["max_depth"];
            $random_state = $_POST["random_state"];
            $n_estimators = $_POST["n_estimators"];
            $params[] = escapeshellarg($max_depth);
            $params[] = escapeshellarg($random_state);
            $params[] = escapeshellarg($n_estimators);
        } elseif ($model == "LinearSVC") {
            $svc_C = $_POST["svc_C"];
            $params[] = escapeshellarg($svc_C);
        } elseif ($model == "LinearSVR") {
            $svr_C = $_POST["svr_C"];
            $svr_epsilon = $_POST["svr_epsilon"];
            $params[] = escapeshellarg($svr_C);
            $params[] = escapeshellarg($svr_epsilon);
        } elseif ($model == "LogisticRegression") {
            $logistic_C = $_POST["logistic_C"];
            $params[] = escapeshellarg($logistic_C);
        } elseif ($model == "LinearRegression") {
            $linear_fit_intercept = $_POST["linear_fit_intercept"];
            $params[] = escapeshellarg($linear_fit_intercept);
        } elseif ($model == "SVM") {
            $svm_kernel = $_POST["svm_kernel"];
            $svm_C = $_POST["svm_C"];
            $params[] = escapeshellarg($svm_kernel);
            $params[] = escapeshellarg($svm_C);
        }
        
        
        // 執行 Python 腳本，並傳遞參數
        $command = "C:\Users\Moroco\Documents\python\FUNAI\.venv\Scripts\python.exe " . escapeshellarg($script_name) . " " . implode(" ", $params);
        echo $command;
        $output = shell_exec($command);
        
        echo "<h3>訓練結果：</h3>";
        echo "<pre>$output</pre>";
    } else {
        echo "<h3>錯誤：無效的模型選擇！</h3>";
    }
    
}
?>