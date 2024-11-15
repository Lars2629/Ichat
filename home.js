

function followBtn(button) {
    // Find the SVG inside the clicked button
    const followIcon = button.querySelector('svg');

    // Replace current SVG with the new one based on the condition
    if (followIcon.getAttribute("height") === "30px") {
        followIcon.setAttribute("height", "32px");
        followIcon.setAttribute("width", "32px");
        followIcon.innerHTML = `
            <defs>
                <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#a01bf7;stop-opacity:1" />
                    <stop offset="100%" style="stop-color:#598BF1;stop-opacity:1" />
                </linearGradient>
            </defs>
            <path d="M516.92-413.03h33.85v-123.89h123.9v-33.85h-123.9v-123.9h-33.85v123.9H393.03v33.85h123.89v123.89ZM306.15-267.69q-24.57 0-41.52-16.94-16.94-16.95-16.94-41.52v-455.39q0-24.58 16.94-41.52Q281.58-840 306.15-840h455.39q24.58 0 41.52 16.94Q820-806.12 820-781.54v455.39q0 24.57-16.94 41.52-16.94 16.94-41.52 16.94H306.15ZM198.46-160q-24.58 0-41.52-16.94Q140-193.88 140-218.46v-489.23h33.85v489.23q0 9.23 7.69 16.92 7.69 7.69 16.92 7.69h489.23V-160H198.46Z"fill="url(#gradient)"/>`;   
    } else {
        followIcon.setAttribute("height", "30px");
        followIcon.setAttribute("width", "30px");
        followIcon.innerHTML = '<path d="M517.5-419.5h22v-124h124v-22h-124v-124h-22v124h-124v22h124v124ZM308-280q-22.24 0-38.12-15.88Q254-311.76 254-334v-440q0-22.24 15.88-38.12Q285.76-828 308-828h440q22.24 0 38.12 15.88Q802-796.24 802-774v440q0 22.24-15.88 38.12Q770.24-280 748-280H308Zm0-22h440q12 0 22-10t10-22v-440q0-12-10-22t-22-10H308q-12 0-22 10t-10 22v440q0 12 10 22t22 10Zm-96 118q-22.24 0-38.12-15.88Q158-215.76 158-238v-462h22v462q0 12 10 22t22 10h462v22H212Zm64-622v504-504Z"/>';
    }
}


// Toggle the notification dropdown visibility
function toggleNotifications() {
    var dropdown = document.getElementById("notifDropdown");
    dropdown.classList.toggle("show");
}

function searchUser() {
    var searchText = $("#search").val();
    if (searchText.length > 0) {
        $.ajax({
            url: 'search_handler.php',
            type: 'GET',
            data: { query: searchText },
            success: function(data) {
                $("#result").html(data);
            }
        });
    } else {
        $("#result").html('');  // Clear the result if the search box is empty
    }
}