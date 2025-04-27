<?php
// 設定 response 為 UTF-8，避免亂碼
header('Content-Type: text/html; charset=utf-8');

// 確認有接收到 POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fileInput'])) {
    // 抓到使用者從 form 傳來的檔案名稱
    $modelFileName = trim($_POST['fileInput']);  // 去除空白
    $Check = explode('_', $modelFileName);
    $modelFileName = $modelFileName . ".pickle";
    if (empty($modelFileName)) {
        die('錯誤：檔案名稱不能為空。');
    }
    // 檢查副檔名
    if (!preg_match('/\.pickle$/', $modelFileName)) {
        die('錯誤：檔案名稱必須是 .pickle 結尾。');
    }

    // 設定存放路徑
    $saveDir = __DIR__ . '/..';
    if (!is_dir($saveDir)) {
        mkdir($saveDir, 0777, true);
    }

    $pyFilePath = $saveDir . '/MLPlay.py';
    if(in_array('CL', $Check)){
        $pyContent = <<<PYTHON
        #CL
        import pickle
        import csv
        import os
        import numpy as np
        
        class MLPlay:
            def __init__(self, ai_name, *args, **kwargs):
                print(ai_name)
                self.model_name = "{$modelFileName}"
                self.result_dir = os.path.join(os.path.dirname(__file__), "..", "result")
                os.makedirs(self.result_dir, exist_ok=True)
                self.result_path = os.path.join(self.result_dir, "model_name.csv")
                self.Nowresult_path = os.path.join(self.result_dir, "Nowresult.csv")
                self.ball_served = False
                self.previous_ball = (0, 0)
                with open(os.path.join(os.path.dirname(__file__), "save", self.model_name), "rb") as f:
                    self.model = pickle.load(f)
            
            def update(self, scene_info, *args, **kwargs):
                if scene_info["status"] == "GAME_OVER" or scene_info["status"] == "GAME_PASS":
                    self.save_model_name()
                    return "RESET"
                
                if not self.ball_served:
                    self.ball_served = True
                    command = "SERVE_TO_RIGHT"
                else:
                    Ball_x = scene_info["ball"][0]
                    Ball_y = scene_info["ball"][1]
                    Speed_x = scene_info["ball"][0] - self.previous_ball[0]
                    Speed_y = scene_info["ball"][1] - self.previous_ball[1]
                    Platform = scene_info["platform"][0]
                    if Speed_x > 0:
                        Direction = 0 if Speed_y > 0 else 1
                    else:
                        Direction = 2 if Speed_y > 0 else 3
        
                    x = np.array([Ball_x, Ball_y, Speed_x, Speed_y, Direction, Platform]).reshape(1, -1)
                    y = self.model.predict(x)
                    if y == 0:
                        command = "NONE"
                    elif y == -1:
                        command = "MOVE_LEFT"
                    elif y == 1:
                        command = "MOVE_RIGHT"
                
                self.previous_ball = scene_info["ball"]
                return command
        
            def reset(self):
                self.ball_served = False
        
            def get_model_info(self):
                return {
                    "model_name": self.model_name
                }
        
            def save_model_name(self):
                model_name_without_extension = os.path.splitext(self.model_name)[0]
                result_data = {
                    "model_name": model_name_without_extension
                }
                file_exist = os.path.isfile(self.result_path)
                with open(self.result_path, mode='a', newline='') as f:
                    writer = csv.DictWriter(f, fieldnames=result_data.keys())
                    if not file_exist:
                        writer.writeheader()
                    writer.writerow(result_data)
        PYTHON;
        
    }else{
        $pyContent = <<<PYTHON
        #RE
        import pickle
        import csv
        import os
        import numpy as np
        
        class MLPlay:
            def __init__(self, ai_name, *args, **kwargs):
                print(ai_name)
                
                self.model_name = "{$modelFileName}"
                
                self.result_dir = os.path.join(os.path.dirname(__file__), "..", "result")
                os.makedirs(self.result_dir, exist_ok=True)
                self.result_path = os.path.join(self.result_dir, "model_name.csv")
                
                self.ball_served = False
                self.previous_ball = (0, 0)
        
                # 載入 model，並且需要更換 model 名稱
                with open(os.path.join(os.path.dirname(__file__), 'save', self.model_name), 'rb') as f:
                    self.model = pickle.load(f)
            
            def update(self, scene_info, *args, **kwargs):
                # 當遊戲結束或遊戲通過時，要求調用 `reset()` 以開始新的一輪
                if scene_info["status"] == "GAME_OVER" or scene_info["status"] == "GAME_PASS":
                    self.save_model_name()
                    return "RESET"
                
                if not self.ball_served:
                    self.ball_served = True
                    command = "SERVE_TO_RIGHT"
                else:
                    Ball_x = scene_info["ball"][0]
                    Ball_y = scene_info["ball"][1]
                    Speed_x = scene_info["ball"][0] - self.previous_ball[0]
                    Speed_y = scene_info["ball"][1] - self.previous_ball[1]
                    
                    if Speed_x > 0:
                        if Speed_y > 0:
                            Direction = 0  # 球往右下
                        else:
                            Direction = 1  # 球往右上
                    else:
                        if Speed_y > 0:
                            Direction = 2  # 球往左下
                        else:
                            Direction = 3  # 球往左上
                    
                    x = np.array([Ball_x, Ball_y, Speed_x, Speed_y, Direction]).reshape((1, -1))  # 展開成一列
                    y = self.model.predict(x)  # 使用 model 預測 Ball X 落點
                    
                    if scene_info["platform"][0] + 20 + 5 < y:
                        command = "MOVE_RIGHT"
                    elif scene_info["platform"][0] + 20 - 5 > y:
                        command = "MOVE_LEFT"
                    else:
                        command = "NONE"
                
                self.previous_ball = scene_info["ball"]
                return command
        
            def reset(self):
                self.ball_served = False
            
            def get_model_info(self):
                return {
                    "model_name": self.model_name 
                }
        
            def save_model_name(self):
                model_name_without_extension = os.path.splitext(self.model_name)[0]
                result_data = {            
                    "model_name": model_name_without_extension
                }
        
                file_exist = os.path.isfile(self.result_path)
                with open(self.result_path, mode='a', newline='') as f:
                    writer = csv.DictWriter(f, fieldnames=result_data.keys())
                    if not file_exist:
                        writer.writeheader()
                    writer.writerow(result_data)
        PYTHON;
    }

    // Python內容，插入 modelFileName
    
    // 寫入 Python 檔案
    file_put_contents($pyFilePath, $pyContent);
    echo "✅ MLPlay.py 已建立成功！<br>";
}

//     // 【可選】自動執行 Python 程式
//     // 注意！這個地方要你的伺服器有安裝 python
//     $pythonPath = 'python3'; // 依照你的伺服器設定，可能是 python 或 python3
//     $command = escapeshellcmd("$pythonPath $pyFilePath");
//     $output = [];
//     $return_var = 0;
//     exec($command, $output, $return_var);

//     // 顯示 Python 執行結果
//     if ($return_var === 0) {
//         echo "🎯 執行成功！輸出結果：<br>";
//         echo nl2br(htmlspecialchars(implode("\n", $output)));
//     } else {
//         echo "⚠️ 執行失敗，請檢查 Python 錯誤。";
//     }
// } else {
//     echo "請透過正確的表單提交。";
// }
//
?>
