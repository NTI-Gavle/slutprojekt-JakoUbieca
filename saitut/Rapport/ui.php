<?php
$asset_base = "/saitut/Rapport/"; 
?>
<link rel="stylesheet" href="<?php echo $asset_base; ?>style.css"> 

<div class="rapport-btn" onclick="toggleRapportPopup()"> <!-- buttno rapport -->
    <span class="rapport-icon">🆘</span>
</div>


<div class="rapport-popup-overlay" id="rapportPopup" style="display:none;">
    <div class="rapport-popup-content">
        
        <div id="rapport-menu-view">
            <div class="rapport-header">
                <h3>Support Center</h3>
                <button class="close-rapport" onclick="closeRapportPopup()">&times;</button>
            </div>
            <div class="rapport-menu-buttons">
                <button class="btn-rapport-menu" onclick="showRapportSubmit()">Send Report</button>
                <button class="btn-rapport-menu" onclick="showRapportList()">My Reports</button>
            </div>
        </div>

        <div id="rapport-submit-view" style="display:none;">              <!--Form-->
            <div class="rapport-header">
                <button class="back-rapport" onclick="openRapportMenu()">←</button>
                <h3>Send a Problem</h3>
                <button class="close-rapport" onclick="closeRapportPopup()">&times;</button>
            </div>
            <form id="rapportForm" onsubmit="submitRapport(event)">
                <label class="rapport-label">Title of the problem:</label>
                <input type="text" id="rapportTitle" class="rapport-input" required placeholder="Example: Chat is not working">
                
                <label class="rapport-label">Description:</label>
                <textarea id="rapportDesc" class="rapport-input" rows="4" required placeholder="Please describe the issue in detail..."></textarea>
                
                <button type="submit" class="btn-rapport-send">Send</button>
                <p id="rapportStatusMessage" style="margin-top: 10px; font-weight: bold; text-align:center;"></p>
            </form>
        </div>

        <div id="rapport-list-view" style="display:none;">                      <!-- Rapport list -->
            <div class="rapport-header">
                <button class="back-rapport" onclick="openRapportMenu()">←</button>
                <h3>My Reports</h3>
                <button class="close-rapport" onclick="closeRapportPopup()">&times;</button>
            </div>
            <div id="rapport-list-container" class="rapport-list-container">

            </div>
        </div>

        <div id="rapport-chat-view" style="display:none;">                      <!-- Chat -->
            <div class="rapport-header">
                <button class="back-rapport" onclick="showRapportList()">←</button>
                <h3 id="chat-report-title" style="font-size: 1.1rem; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Chat</h3>
                <button class="close-rapport" onclick="closeRapportPopup()">&times;</button>
            </div>
            
            <div id="chat-report-desc" class="chat-desc-box"></div>
            
            <div id="rapport-chat-messages" class="rapport-chat-messages">
            </div>
            
            <div id="rapport-chat-input-area" class="rapport-chat-input-area">
                <input type="text" id="rapport-msg-input" class="rapport-input" style="margin-bottom:0;" placeholder="Type your message...">
                <button class="btn-rapport-send" style="width: auto; padding: 10px 20px;" onclick="sendRapportMessage()">Send</button>
            </div>
            <div id="rapport-chat-actions" style="margin-top:10px; text-align:center;">
                <button class="btn-rapport-close-issue" onclick="closeRapportIssue()">Mark as Resolved (Close)</button>
            </div>
        </div>

    </div>
</div>

<script src="<?php echo $asset_base; ?>script.js"></script>
