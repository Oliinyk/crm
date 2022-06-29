const estimateFormat = {
  toMin: (data, workingHours) => {
    const DATE_REG = /[^0-9, dhmDHM]/ //RegExp
    if (DATE_REG.test(data)) { //checking characters for compliance with the rule
      return -1
    }
    let days = 0
    let hours = 0
    let min = 0
    let index = null
    if (data.search(/[^0-9, ]/) === -1) { //check for numbers
      return -1
    }
    const estimate = data.split('')
    index = estimate.findIndex(item => item === 'd')
    if (index !== -1) days = parseInt(estimate.splice(0, index).join('').replace(/[^0-9]/, '')) //reading days
    index = estimate.findIndex(item => item === 'h')
    if (index !== -1) hours = parseInt(estimate.splice(0, index).join('').replace(/[^0-9]/, '')) //reading hours
    index = estimate.findIndex(item => item === 'm')
    if (index !== -1) min = parseInt(estimate.splice(0, index).join('').replace(/[^0-9]/, '')) //reading minutes
    if (min > 0) {
      hours += parseInt(min / 60) //translation of minutes into hours
      min = parseInt(min % 60) //getting minutes from the rest
    }
    if (hours > 0) {
      days += parseInt(hours / workingHours) //transfer of hours in days
      hours = parseInt(hours % workingHours) //getting days from the rest
    }
    let minSelected = min + (hours * 60) + (days * workingHours * 60)
    return minSelected
  },
  toHour: (min) => {
    let hours = 0
    if (min > 0) {
      hours += parseInt(min / 60) //translation of minutes into hours
      min = parseInt(min % 60) //getting minutes from the rest
    }
    return `${hours ? hours + 'h' : ''} ${min ? min + 'm' : ''}` //result output
  },
  toText: (min, workingHours) => {
    let days = 0
    let hours = 0
    if (min > 0) {
      hours += parseInt(min / 60) //translation of minutes into hours
      min = parseInt(min % 60) //getting minutes from the rest
    }
    if (hours > 0) {
      days += parseInt(hours / workingHours) //transfer of hours in days
      hours = parseInt(hours % workingHours) //getting days from the rest
    }
    return `${days ? days + 'd' : ''} ${hours ? hours + 'h' : ''} ${min ? min + 'm' : ''}` //result output
  },
  toFormat: (text, workingHours) => {
    const DATE_REG = /[^0-9, dhmDHM]/ //RegExp
    if (DATE_REG.test(text)) { //checking characters for compliance with the rule
      return -1
    }
    let days = 0
    let hours = 0
    let min = 0
    let index = null
    if (text.search(/[^0-9, ]/) === -1) { //check for numbers
      return -1
    }
    const estimate = text.split('')
    index = estimate.findIndex(item => item === 'd')
    if (index !== -1) days = parseInt(estimate.splice(0, index).join('').replace(/[^0-9]/, '')) //reading days
    index = estimate.findIndex(item => item === 'h')
    if (index !== -1) hours = parseInt(estimate.splice(0, index).join('').replace(/[^0-9]/, '')) //reading hours
    index = estimate.findIndex(item => item === 'm')
    if (index !== -1) min = parseInt(estimate.splice(0, index).join('').replace(/[^0-9]/, '')) //reading minutes
    if (min > 0) {
      hours += parseInt(min / 60) //translation of minutes into hours
      min = parseInt(min % 60) //getting minutes from the rest
    }
    if (hours > 0) {
      days += parseInt(hours / workingHours) //transfer of hours in days
      hours = parseInt(hours % workingHours) //getting days from the rest
    }
    return `${days ? days + 'd' : ''} ${hours ? hours + 'h' : ''} ${min ? min + 'm' : ''}` //result output
  },
}
export default estimateFormat
